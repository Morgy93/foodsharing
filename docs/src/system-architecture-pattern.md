# System architecture pattern

## Introduction

The foodsharing platform is a old system, which have different migrations running.
This chapter describes the architecture of the PHP backend.

## Overview

The foodsharing platform provide a web interface which is communicating with the backend via RestAPI and websockets.
The backend provides a RestAPI which use transactions (services) for realization of business logic.
The websockets are managed by a "chat" server, this server exchange important information via RestAPI and redis.

The RestAPI use [permissions](backend-permissions-roles.md) to check rights of a user to execute actions or get information.
The permission check basic information came from session instances or transactions.

The transaction use gateways to get information from the database and can store them into the session.

~~~plantuml
@startuml
skinparam linetype ortho

[Frontend]
node [Backend] {
node [php] {
[RestAPI]
[Permission]
[Session]
[Transactions]
[Gateway]
}
database DB
node redis {
    database Cache
    database Queue
}
node [nodejs] {
[chat]
}
}

[Frontend] -d-> [RestAPI]
[Frontend] -d-> [chat]
[chat] -d-> [Cache]
[Transactions] -d-> [Cache]

[RestAPI] -> [Transactions]
[Transactions] -d-> [Gateway]
[Gateway] -d-> DB


[RestAPI] --> [Permission]
[RestAPI] --> [Session]

[Permission] -> [Session]
[Transactions] -> [Session]
[Permission] -> [Transactions]

@enduml
~~~

## Components

### Frontend

The frontend is a web interface. This web interface should use the RestAPI to fetch information or run actions. This allows to replace the web interface by native apps or to build other services on top.

### Backend

The backend is a monolithic application which manages all business logic. The system use therefore Symfony as base framework and added many dependencies which help to reduce development effort.
The software is structured by following pattern.

#### RestAPI

The RestAPI maps the business logic to an self describing API, so that the other API users like iOS, android app developer and the web interface developer can use it in the same way and kann understand it.

The implementation details can be found in [chapter controllers](php-controllers.md)

#### Transactions

A transaction/service is a part of the foodsharing platform which maps user input data into the representation like the database requires.
The transaction implement the core business logic. Therefore, it may use gateways or other services to realize it.
A transaction does not check the permissions, so that other services can use it with users which may do not have the permissions.

For details about transaction can be found in the [chapter transactions](php-transactions.md).

#### Gateways

A gateway is the abstraction for the database table. It is used to CRUD related methods. 
The gateway should encapsulate all database-specific parts, so that a replacement by another database is possible or that the developer knows that all SQL statements are inside these classes.

For details about gateways can be found in the [chapter gateways](php-gateways.md).

#### Permissions

A permission class provides functions to check current user permission for an RestAPI action or other controllers.
It ensures that the user is restricted to the possibilities the user role or group should have.
The permission are a kind of transaction class and can use services, gateways or the session to access imported information.
The [chapter roles and permissions](Permissions-and-Roles) gives an overview about the existing permissions.

#### Sessions

The foodsharing plattform uses a very central component to manage user related information. The session of the current user contains often used information to increase performance of the request handling.


### Database

The database is the main storage for the permanent required information.
The description of it can found in the dev documentation

- [Use of database with codebase](https://devdocs.foodsharing.network/database.html)
- [Description of all tables](https://devdocs.foodsharing.network/database-tables-columns.html)


### Cache (Redis)

Is used by php Session handler and some other parts Mem + DB.

### Old stuff

- The old basic system used a MVC-pattern to separate visualization from the data and the control. This is visable by Xhr, control, view or model classes.
- In some foodsharing platform modules are already RestAPI use, many of them use Gateways directly without transactions.
- Other parts use the permissions to check states which belong to the business logic.
- Gateway sometime contain business logic which belong into services.
- Different Gateways contain same code to provide information.

