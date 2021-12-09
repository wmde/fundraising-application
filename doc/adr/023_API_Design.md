# Fundraising Application API Design

Date: 2021-11-30

Deciders: Gabriel Birke, Corinna Hillebrandt, Abban Dunne

Technical Story:

## Status

Accepted

## Context

The decision was made in [ADR 21](https://github.com/wmde/fundraising-application/blob/main/doc/adr/021_Single_or_Multi_Page_Application_Architecture.md) to move towards an API and Single-Page-Application. This document covers the decisions made around the design of the API.

## Decision Drivers
* Maintainability - The API should be easy to maintain.
* Consistency - The API controllers should be consistent in how they handle requests and send responses.
* Accessibility - The API should be well documented.
* Separation of concerns - We want to separate our domain classes
	(entities, use cases) from the framework. Domain classes MUST NOT
	have framework-specific annotations or attributes.

## Considered Options
We considered 3 options:
* Build it ourselves.
* The [Symfony Json bundle](https://github.com/symfony-bundles/json-request-bundle).
* The [FOSRest Bundle](https://github.com/FriendsOfSymfony/FOSRestBundle).

The Json bundle combines body items into the `request->get()` bucket making the JSON body data indistinguishable from query parameters which rules it out.

The FOSRest Bundle is too big and integrated into Symfony, which means it's not orthogonal enough for our requirements.

We decided to build it ourselves. This has the advantage of keeping it concise, and as long as we follow our design rules it will be consistent and maintainable.

## Decisions
* We will use Swagger for the API documentation. This will eventually be the source of truth.
* We will eventually use the Swagger yaml files to generate automated tests.
* The API URL schema should be REST-ish: We define resources like "donation", "comment", "membership", "payment", etc. and use HTTP verbs to retrieve or change the resources. Internally, we map each resource to a controller and each HTTP verb to an action in the controller. We call these "resource controllers".
* Symphony has no resource controllers, instead we follow the [Laravel style guide](https://laravel.com/docs/8.x/controllers#actions-handled-by-resource-controller) for controller [method naming](#controller-actions).
* Response Status' are HTTP codes.
* Responses and Requests are JSON.
* POST/PUT requests contain the data as a JSON object in the Request body.
* Responses also return JSON in the body. 
* API response messages are verbose snake_case strings. This means they can be read by humans and machines.
* UseCases should return data objects and not domain entities.
* Entity create and save should return the updated entity in the response.
* Keys in the responses are camelCase. This allows us to serialise our PHP data entities without having to transform the property names into snake_case.

### Controller Actions

| HTTP Method | URL | Controller Method |
|:--|:--|:--|
| GET | `/entity` | `index()` |
| POST | `/entity` | `store()` |
| GET | `/entity/{id}` | `show()` |
| PUT | `/entity` | `update()` |
| DELETE | `/entity/{id}` | `destroy()` |

## Links
* https://github.com/NationalBankBelgium/REST-API-Design-Guide
* https://swagger.io/
* https://laravel.com/docs/8.x/controllers#actions-handled-by-resource-controller
