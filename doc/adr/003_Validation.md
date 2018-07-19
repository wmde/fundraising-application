# 003 Validation

Date: 2018-07-018

## Status

Accepted

## Context

When the team for the re-write of the Fundraising formed in 2016, we discovered that team members had different approaches to do validation:

* Use an established library, like [Symfony Validation](https://symfony.com/doc/current/validation.html).
* Write our own validation logic.

The arguments in favor of writing our own logic were:

* We don't want to bind our domain layer to a concrete validation library implementation.
* The individual validations - checking for required fields in most cases - are so simple that using an external library would make the validation more complicated.
* We don't know the "maintenance cycles" of the library, either we need to constantly update or the library is not maintained properly.
* Every developer would have to learn the API of the external library.

At the start of the project we did not know where we should put the validation logic:

* At the framework/presentation layer, forcing us to create valid, fully formed domain objects as input for use cases.
* At the use case layer, making validation part of the use case.

## Decision

For each use case we write a validator class that checks the `Request` value object of that use case. The validator class must ensure that the use case can create valid domain objects from the request object. The validator class uses simple `if` checks and no external framework.

We return result data structures from validation classes. The result data structures that have some way of communicating to the framework layer what input caused the validation error. If necessary, one input can have more than one validation error.

Validation error names are language-independent unique strings in `snake_case`. When we need to translate those error codes, we put the translations in the file [`validations.js`](https://github.com/wmde/fundraising-frontend-content/blob/test/i18n/de_DE/messages/validations.json) in the [content repository](https://github.com/wmde/fundraising-frontend-content). We don't  map every error to a translation, we can write frontend layer code that summarizes the errors or maps them in a different way.

## Consequences

We did not manage to introduce a consistent interface for validators and validation results. We took some inspiration from the `ConstraintViolation` classes of Symfony Validation and created the [FunValidators repository](https://github.com/wmde/fun-validators/) that contains some abstracted, low-level validators and the `ValidationResult` class for collecting constraint violations.

Sometimes our validators return booleans or fieldname => error pairs instead of subclassing `ValidationResult`. This was the result of the approach "what would be the simplest solution that works?"
