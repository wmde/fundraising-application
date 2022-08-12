# Client-Side Validation

Date: 2022-08-12

Deciders: Abban Dunne, Corinna Hillebrand, Gabriel Birke

## Status
Accepted

## Context

We want to give users quick feedback if their form input is valid, without having to submit the form and waiting for the page reload.

## Decision drivers

We don't want to duplicate complex server-side business logic (e.g. minimum
yearly membership fees dependent on the address type of the member) on the client,
because that would mean duplicating work in different programming
languages and having tools and processes to check that the validation
works the same both on the client and the server.

We *can't* perform the bank data validation on the client side, because
it's a PHP extension written in C and comes with a large data file.

We want the feedback for the user to be as fast as possible. This means
reducing the server-side communication to a minimum.



## Decision

Our client-side code follows the following pattern:

1. Pre-validate on the client side for empty fields and invalid input
   (pattern matching, length checking), whenever the user changes a field.
   Give immediate feedback.
2. When the user has filled in all fields on a page, send the fields to
   the server for validation. The validation result from the server
   overrides the validation performed by the client.
3. When the user submits the form, the code checks if the server has
   successfully validated the fields. If the background validation is
   ongoing, the code delays the submission of the fields until all
   validation requests have returned.

The messages we show to the user should come from the client-side code. In
the error case, the server JSON response contains a `messages` field (of
type object), with a mapping of field names (or rather "source of error")
to error causes. The client-side code should care about the field names,
and not display any error-case data from the server, but should use its
own I18N library to determine the message content.

## Consequences

The client-side code becomes more complex:

* Validation state becomes ternary (`INCOMPLETE/VALID/INVALID`) instead of binary (`VALID/INVALID`).
* We have to keep track of ongoing validation requests and need to cancel them if the user gives new input. 
  We have to prevent form  submission until all requests have finished.
* Our state-handling code needs to interact with the server, updating the
  validation state when the response comes back.

We are planning to make the client-side code a Single-Page-Application
(see [ADR 24](./021_Single_or_Multi_Page_Application_Architecture.md)). At
the time of this writing, the validation end points expect
`multipart/form-data` encoded content. The new API should process the
fields as a JSON body.

## History

* 2018-07-20 - Accepted basic version
* 2022-08-12 - Updated version with more architectural detail fewer
	implementation details.

