# Building objects in the main factory

Date: 2020-04-30

## Status: 

Accepted

## Context and Problem Statement

* How do we create instances of our classes in the application? 
* How to pass dependencies to the constructors?
* How to ensure that some objects can be shared, while others are created every time they are needed?

## Decision Drivers

* We want the object creation to be type-safe to be able to use the code intelligence features of the IDE,
  the strict type checking of PHP and the static analysis capabilities of PHPStan.
* We want the object creation to support adhering to the SOLID principles:
  * The single responsibility principle will lead to lots of classes, depending on each other in a hierarchy. 
    Also, our instance creation solution should separate the concerns of instantiation from what the classes are doing.
  * The Open-Closed principle, when solved with interfaces, makes it necessary to create different instances for the 
    same interface and support switching out instances, e.g. for testing.
  * The Dont-Repeat-Yourself principle means we should have one place where classes are instantiated.
* The Edge2Edge and integration tests need an environment that resembles the production environment as closely as 
  possible, while still being able to switch out services to isolate from side effects (database contents, filesystem,
  randomness, date, etc)
* We want to keep the public API of our code (the methods exposed to the web and command line framework code) as small 
  as possible. Ideally, the API exposes only the use cases.  

## Considered Options

* Ad-hoc instantiation with `new`
* Using the Pimple dependency injection container that comes with the Silex framework
* Creating a central factory

## Decision Outcome / Consequences

We chose the "Central Factory" (a class called `FunFunFactory`) because type-safety and IDE comfort (auto-completion) 
trumped the concerns about boilerplate. We also saw not binding to a specific library or framework a plus. 

We're still using the Pimple DIC internally to get shared objects. We wrapped the access to instances in Pimple with type-safe in getters.
In hindsight, this use of Pimple as a holder of shared objects turned out to be a bad
idea because the creation functions (how we build objects) are too far away from the code that requests them and there 
is no easy way to jump from a getter to the creation function. When we realized that, we switched from Pimple to the 
`createSharedObject` function, but did not refactor all creation functions.

We use naming conventions to give the consumers a hint if an object is shared (a singleton) or newly created: methods
starting with `get` return shared objects, methods with `new` return "fresh" objects on every call.

We extracted the use cases into bounded contexts at a late stage of the project. We attempted to split the factory 
for use cases into  "ContextFactory" classes, but that attempt was half-hearted and difficult to do, since some of the 
underlying dependencies (db connection, entity manager) only make sense when an application uses a bounded context. 

The `FunFunFactory` is has become long and disorganized. Unfortunately, because we did not pay attention to boundaries 
across bounded context or abstraction layers. We did not apply the naming conventions consistently.

`FunFunFactory` contains setters that make it possible to switch out implementations for testing. They are also used to set
up environment-specific services like logging (See the classes in the `WMDE\Fundraising\Frontend\Factories\EnvironmentSetup` 
namespace). From an architectural perspective this is bad, since it potentially allows controllers to switch out their 
services, which shouldn't be allowed.   


## Pros and Cons of the Options

### Ad-hoc instantiation

Using `new` or a static construction method (see Matomo code base) whenever a class needs a service leads to severe problems:

* Dependencies are not configurable, making the class harder to test
* We duplicate instantiation all over the code base, leading to inconsistencies and 
  "[shotgun surgery](https://en.wikipedia.org/wiki/Shotgun_surgery)" whenever the constructor of a class changes.


### Pimple dependency injection container

[Pimple](https://pimple.symfony.com) uses the [`ArrayAccess`](https://www.php.net/manual/de/class.arrayaccess.php) 
interface to organize dependencies. You create objects inside anonymous functions, assigned to an array key. When code 
accesses the array key, Pimple calls the creation function once and returns the created instance (caching it for future accesses).
Creation functions get the container as their parameter so they can get their dependencies.   

* Good, because all classes are shared by default.
* Good, because it's well-integrated with the Silex framework.
* Bad, because there is no type checking and no type hints. This could be partially overcome with extensive `@property` 
  docblocks, but those would be far away from the creation functions. 
* Bad, because there is no concept of public and private instances.


### Central Factory

* Good, because it's type safe
* Good, because you can follow the object tree construction with "jump to definition" movements in our editor/IDE.
* Good, because you can define what's public and what's private 
* Bad, because you will have a factory function for almost every class
* Bad, because it does not enforce much structure or allow other patterns (singleton, wrappers)

## Links

* [The drawbacks of our choices will be improved upon by ADR 014](014_Object_Construction_Improvements.md)


