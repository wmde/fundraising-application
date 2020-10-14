# Controller Naming Schema

Date: 202-10-13

Deciders: Gabriel Birke, Abban Dunne, Corinna Hillebrand

Technical Story: https://phabricator.wikimedia.org/T198484 

## Status

Proposed

## Context and Problem Statement

To prepare for the transition from the Silex to the Symfony framework we need to convert the anonymous route functions to controller classes. This document is about how to name those classes and their methods.

Some anonymous route functions already had classes, called "RouteHandlers", with a single method called `handle`. Some controllers have adopted this scheme as their action method name, while others have more informative method names.

The state of the controller naming(October 2020): Most of the controllers have an `ActionSentenceController::action` schema with only one action, two controllers, `ApplyForMembershipController` and `ContactController` have a ``NounController::action`` schema. 

Some of the code in the controllers (e.g. validation) might become obsolete if we use more Symfony features, making the controllers shorter. 

## Decision Drivers 

* **Consistency** - We want to have some general rule of thumb to apply to all controller classes. 
* **Developer Experience** 
  * We want to jump quickly to a controller class via the code search features of the IDE.
  * We want the controllers to be as short as possible, avoid scrolling. 
  * We want to easily navigate to the right controller in the file system, keeping the number of files in a directory/namespace as small as possible.
* **Following established standards** - We want to do what other frameworks are doing
* **Gradual refactoring** - The naming changes must not force the transition form Silex to Symfony by choosing a pattern that's incompatible with Silex.

## Considered Options

* Leave controllers as-is
* Adopt `NounController::action` schema, each controller may have multiple actions
* Adopt `ActionSentenceController::action` schema, with each controller having one single action.
* Adopt `ActionSentence::action` schema, dropping the `Controller` suffix

## Decision Outcome

TBD: Chosen option: "", because (justification. e.g., only option, which meets k.o. criterion decision driver | which resolves force force | … | comes out best (see below)).


## Pros and Cons of the Options

### Leave controllers as-is

Most controllers already follow an `ActionSentenceController::action` pattern, with inconsistent `action` naming and number of actions. But it's not too bad.

* Good, because we don't need to change anything
* Bad, because we're inconsistent 
* Bad because the inconsistency forces us and future devs to pay attention/think when creating new controllers.

### `NounController::action` 

Group controllers by "Domain" (e.g. Donation, Payment, Comment, Membership, StaticPages) and have multiple actions, some of those calling a use case.  

* Good, because it's consistent
* Good, because it's an established pattern in other web frameworks 
* Good, because the amount of classes/files is small
* Bad, because the classes become long, needing a lot of scrolling
* Bad, because we have to change most of the controllers (and the corresponding routes). This option has the biggest effort.
* Bad, because our E2E test structure won't reflect the class structure anymore (`@covers` annotations for the same controller split across tests).
* Bad, because the code quality scores will go down because the classes
  are too big.
* Bad, because it's hard to find a single action via code search in the IDE

### `ActionSentenceController::action`

Each route of the application has one class with `ActionSentenceController::action` schema, with each controller having one single action.
 
The action name can one of the following:

    * `handle`
    * `index`
    * `__invoke` (magic method, https://symfony.com/doc/current/controller/service.html#invokable-controllers )

Single-action controllers that have multiple public methods for the same action but in multiple output formats (e.g. `ListCommentsController`) stay as they are. They will be refactored later using Symfony's `_format` feature.

`__invoke` is incompatible with the route definition of Silex, so we first might choose to standardize on `index` or `handle` and change the method name and route definition later, to have shorter route definitions.  

* Good, because it's consistent
* Good, because it fits well with the URL schema we want to establish. See [ADR 005, "URL Schema"](005_URL_Schema.md).
* Good, because the effort is reasonable, we only add 3-5 new controllers when splitting.
* Good, because we can quickly find the controllers with the code search of the IDE.
* Bad, because we have 30+ controllers, making filesystem navigation and scanning of the files tedious. We can mitigate that by introducing Domain-related namespaces.

### `ActionSentence::action`

Similar to `ActionSentenceController::action`, but losing the `Controller` suffix, because all controllers are in the `Controller` namespace.
