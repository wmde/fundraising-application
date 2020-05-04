# Object Construction Improvements

Date: 2020-04-30

Deciders: Gabriel Birke, Abban Dunne, Corinna Hillebrand

## Status

Proposed

## Context and Problem Statement

Creating a central factory has served us well, but has drawbacks, as
outlined in [ADR 013](013_Main_Factory.md). This document is about
exploring ways to mitigate those drawbacks, while keeping the benefits of
type-safety and an architecture based on the SOLID principles.

## Decision Drivers

* FunFunFactory is a long class with too many extension
	points for tests and exposing too many instances.
* Ideally, the class should not have any branching logic. Without
	branching logic, the only test we need for this class is that it
	produces the right instances. We do this with our edge-to-edge
	(integration) tests.
* We will switch our web framework layer from the discontinued Silex
	framework to Symfony. Symfony comes with a powerful [Dependency
	Injection Container
	(DIC)](https://symfony.com/doc/current/components/dependency_injection.html)
	that has the same purpose as our factory.


## Considered Options

* Refactor FunFunFactory, inject it as a service into the controllers.
* Drop FunFunFactory and create Symfony DI service configurations instead 

## Decision Outcome

The presented options are don't exclude each other, they are  the start
and end state of a refactoring. In reality, we will start with injecting
the FunFunFactory (without refactoring it) into the controllers. The next
step is to swap the factory and inject the services instead, defining the
FunFunFactory as the service creating factory in the service configuration
file. Over time, we will define more and more services in the service
configuration, extracting them from FunFunFactory, until we can delete it. 

## Pros and Cons of the Options

### Refactor FunFunFactory

At the moment, the FunFunFactory does too much:

* Provide presentation services for the controllers
* Provide use cases for the controllers, across bounded contexts
* Provide "shared" services (logging, notification, entity manager) for
	all bounded contexts.

We could split the big factory into smaller ones - a `PresenterFactory`
for the controllers, a `SharedServicesFactory` for the shared services,
and a `ContextFactory` for each bounded context, with the
`SharedServicesFactory` passed in as a dependency.

We could either forgo the default Symfony application structure, trying to
build the controllers with a `ControllerFactory` integrated in a custom
front controller.

Alternatively, we could inject the factories as dependencies into the
controllers.

* Good, because the slightly anemic `ContextFactory` classes that are
	already implemented will show the dependencies of each context better.
* Good, because we will have more explicit knowledge of the layers and
	contexts, with each one having a smaller public interface.
* Good, because we would still have type-safety and autocompletion
	everywhere.
* Bad, because each context will have its own interface for the
	`SharedServicesFactory`.
* Bad, because the setup hides the dependencies of the controllers,
	creating the ["Service Locator" anti-pattern](https://blog.ploeh.dk/2010/02/03/ServiceLocatorisanAnti-Pattern/).
* Bad, because it goes "against the grain" of the Symfony framework,
	creating mental load if we try to figure out how to use Symfony
	without its DIC component.

### Drop FunFunFactory, use Symfony DIC service configuration

* Good, because we can [visualize our dependency tree](https://symfony.com/doc/current/service_container/debug.html) instead of jumping from initialization to initialization function.
* Good, because we will have less boilerplate code. Most classes can use
	[autowiring](https://blog.ploeh.dk/2010/02/03/ServiceLocatorisanAnti-Pattern/), we only need to define which interfaces use which concrete implementation.
* Good, because the service configuration format offers a domain-specific language for advanced 
   dependency injection concepts like decorating services, having shared
   services, etc, making them explicit. In the FunFunFactory these
   patterns are implicit.
* Bad, because the PHP type system will no longer make sure that we inject
	the correct services. We can mitigate this by [linting the container
	configuration](https://symfony.com/doc/current/service_container.html#linting-service-definitions) during CI.
* Bad, because this is a "big bang" rewrite of a crucial part of the
	application. We don't have a good safety net if we miss something.
	The rewrite a highly iterative process with pitfalls.
* Bad, because we lose "debuggability" - we can no longer step through the
	initialization process of classes as easy as with the factory. To
	mitigate this, all building code with branches should be moved into
	dedicated, unit-tested factories.


