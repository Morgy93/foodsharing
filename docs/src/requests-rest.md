## REST API

The more modern way to build our api is a [REST api](https://symfony.com/doc/master/bundles/FOSRestBundle/index.html) by FOS (friends of symfony).
The documentation of the REST api endpoints is located at the definition of the endpoints and can be nicely viewed on (https://beta.foodsharing.de/api/doc/).

In the [documentation](https://symfony.com/doc/current/bundles/NelmioApiDocBundle/index.html) you can read how to properly include the documentation.
A good example can be found in `/src/RestApi/ForumRestController.php`.
<!-- TODO: how is this created? -->

In the [Code quality page](code-review.md) we have some notes on how to define the REST API Endpoints.

The javascript code that sends REST API requests is found under `/client/src/api` and is used by other javascript by [import](javascript.md).

All php classes working with REST requests are found in [`/src/Modules/RestApi/<..>RestController.php`](https://symfony.com/doc/current/controller.html).
This is configured in [`/config/routes/api.yml`](https://symfony.com/doc/current/bundles/FOSRestBundle/5-automatic-route-generation_single-restful-controller.html).
There it is also configured, that calls to `/api/` are interpreted by the REST api, e.g.
```
https://foodsharing.de/api/conversations/<conversationid>
```
This is being called when you click on a conversation on the „Alle Nachrichten“ page.

REST is configured via [annotations](https://symfony.com/doc/master/bundles/FOSRestBundle/annotations-reference.html) in comments in functions.
  - `@Rest\Get("subsite")` specifies the address to access to start this Action: `https://foodsharing.de/api/subsite"
  - `@Rest\QueryParam(name="optionname")` specifies which options can be used. These are found behind the `?` in the url: `http://foodsharing.de/api/conversations/687484?messagesLimit=1` only sends one message.
  - Both `Get` and `QueryParam` can enforce limitations on the sent data with `requirement="<some regular expression>"`.
  - `@SWG\Parameter`, `@SWG\Response`, ... create the [documentation](https://symfony.com/doc/current/bundles/NelmioApiDocBundle/index.html) (see above)

Functions need to have special names for symfony to use them: the end with `Action`.
They start with a permission check, throw a `HttpException(401)` if the action is not permitted.
Then they somehow react to the request, usually with a Database query via the appropriate Model or Gateway classes.

During running php, the comments get translated to convoluted php code.
REST also takes care of the translation from php data structures to json.
This json contains data. Errors use the [error codes of http-requests](https://en.wikipedia.org/wiki/List_of_HTTP_status_codes).

While reading and writing code a (basic) [manual](https://symfony.com/doc/master/bundles/FOSRestBundle/index.html)
and an [annotation overview](https://symfony.com/doc/master/bundles/FOSRestBundle/annotations-reference.html) will help.


# Handling of big request body

The foodsharing plattform uses sometimes big request bodies (e.g  create/update Store, create and/or update region/working groups).

For a better OpenAPI documentation and to reduce boring repeating (often error prone) conversion code or validation code the following parts can be used.

## Introduction

The implementation of an RestAPI endpoint action typically starts with a RestAPI description with annotations for (Swagger-php)[http://zircote.github.io/swagger-php/] integrated by (Symfony via NelmioApiDocBundle)[https://symfony.com/bundles/NelmioApiDocBundle/current/index.html] followed by FOSBundle endpoint type description.

A typical endpoint follows the following steps.

~~~plantuml
@startuml
Client -> EndpointAction: "POST create /api/store with request body"
EndpointAction -> Request: "read content"
Request --> EndpointAction
EndpointAction -> EndpointAction: "Validate content"
EndpointAction --> EndpointAction: "no errors"

EndpointAction -> Transaction:"Run business logic"
Transaction -> Gateway: "Get data from database"
Gateway --> Transaction: "A value"
Transaction -> Gateway: "Store new data to database"
Transaction --> EndpointAction: "Business logic execution complete"
EndpointAction --> Client: Response
@enduml
~~~

## Request convertation

The request body contains a typical JSON object with different elements.
The extraction of this information can be done by `@RequestParam()`. For many JSON elements it is a lot of conversion code. 
With the `@ParamConverter()` the elements are converted directly into an object.

The `@ParamConverter()` requires therefor a class definition. The converter generates an object of the class and fills all fields with the information found in the request body.
The nice benefit is that the class definition member phpdoc strings are used for the OpenAPI documentation.

## Validation

The conversion to the class does not mean that all restrictions of the values are fulfilled.
This can be checked manually or by the [symfony validation libary](https://symfony.com/doc/current/validation.html). 

The validation library uses the annotations to descibe rules for validation of the class members.
The endpoint action only need to check if error are detected and can throw an bad request error.

If the validation was succesful the business logic like an transaction can be used and the sanitation of the RestAPI RequestBody content is solved in a typical Symfony way.


