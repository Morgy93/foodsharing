# System architecture pattern

## Introduction

The foodsharing platform is a old system, which have different migrations running.
This section tries to describe my understanding of the provided architecture and tries to filter the old found stuff.

## Overview

The system provide a web interface which is communicating with the backend via RestAPI and websockets.
The backend provides a RestAPI which use transactions (services) for realization of buisness logic.
The websockets are managed by a chat server, this server exchange important information via RestAPI and redis (cache). (The chat server stuff is not verified yet).

The RestAPI use [permissions](backend-permissions-roles.md) to check rights of a user to execute actions or get information.
The permission check basic information came from session instances or transactions.

The transaction use gateways to get information from the database and can store them into the session.

### Old stuff

The old basic system used a MVC-pattern to separate visualization from the data and the control. This is visable by Xhr, control, view or model classes.
In some migrated modules with RestAPI are Gateways directly. Other parts use the permissions to check states which belong to the buisness logic.

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

The frontend is a web interface, this web interface should use the RestAPI to fetch information or run actions. This allows to replace the web interface by native apps or to build other services on top.

[] Add technologies

### Backend

The backend is a monolithic application which manages all business logic. The system use therefore Symfony as base framework and added many dependencies which should help to reduce development effort.
The software is structured by following pattern.

[] Add more details about the used technologies.

#### RestAPI

The RestAPI maps the business logic to a self describing API, so that the other API users like android app developer and the web interface developer understand the meaning.

[] Add link to guideline for RestAPI expectations (OpenAPI doc, resource, and actions via HTTP methods)

The implementation details can be found in [chapter controllers](php-controllers.md)

#### Transactions

A transaction service is a part of the system which maps user input data into the representation like the database requires. Therefore, it may use gateways or other services to combine them into a business logic.
A transaction does not check the permissions, so that other services can use it with users which may do not have the permissions.

For details about gateways can be found in the [section](php-transactions.md).

#### Gateways

A gateway is the abstraction for the database table. It is used to CRUD related methods. 
The gateway should encapsulate all database-specific parts, so that a replacement by another database is possible or that the developer knows that all SQL statements are inside these classes.

For details about gateways can be found in the [section](php-gateways.md).

[] Provide examples for DTO or selects with data from different tables.
[] Add relation between Insert/Update/delete in Gateway (it should be only one owner and one gateway should not represent multiply tables)

#### Permissions

A permission class provides functions to check current user permission for an RestAPI action or other controllers.
It ensures that the user is restricted to the possibilities the user role or group should have.
The permission are a kind of transaction class and can use services, gateways or the session to access imported information.
The [chapter roles and permissions](Permissions-and-Roles) gives an overview about the existing permissions.

#### Sessions

The old system uses a very central component to manage user related information. The session of the current user contains often used information to increase performance of the request handling.

[] Need to discuss the future of this. It is not stateless like a RestAPI typical is.

### Database

The database is the main storage for the permanent required information.
The description of it can found in the dev documentation

- [Use of database with codebase](https://devdocs.foodsharing.network/database.html)
- [Description of all tables](https://devdocs.foodsharing.network/database-tables-columns.html)


### Cache (Redis)

