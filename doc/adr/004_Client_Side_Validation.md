# Client-Side Validation

Date: 2018-07-20

## Status
Accepted

## Context

We wanted to give users quick feedback if their donation is valid, without  having to submit the form and waiting for the page reload.

We don't want to duplicate the server-side business logic on the client.

We *can't* perform the bank data validation on the client side, as it's a PHP extension written in C and comes with a large data file.

## Decision

We do all client-side validation via AJAX requests to the server.

To avoid premature error messages on the client, we delay sending the data to the server until the user has filled out all necessary fields.

## Consequences

The client-side code becomes more complex:

* Validation state becomes ternary (`INCOMPLETE/VALID/INVALID`) instead of binary (`VALID/INVALID`).
* We have to keep track of ongoing validation requests and need to cancel them if the user gives new input. We have to prevent form submission until all requests have finished.
* We need to add code for handling asynchronous changes to the simple, synchronous [Flux architecture](https://www.atlassian.com/blog/software-teams/flux-architecture-step-by-step). We do that with [redux-promise](https://www.npmjs.com/package/redux-promise).

We don't have duplicated code and effort and don't need tools or processes to keep client and server-side validation code in sync.
