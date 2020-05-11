# Use Semantic Versioning for all dependencies 

Date: 2020-05-13

Deciders: Gabriel Birke, Abban Dunne, Corinna Hillebrand

## Status

Accepted

## Context and Problem Statement

We need a way to deploy specific versions of our bounded contexts, having
reproducible releases of our software in testing and production .

## Decision Drivers

* We want to have frequent releases of the software, adding new features
	etc.
* We want to keep our dependencies up to date on a monthly basis, using
	`composer update`
* We want to have reproducible versions of our software - `composer
	install` must install the same code on the CI machine, each 
	developers machine, the user acceptance environment and the production
	environment.
* We want to have a trunk-based development process, where the current
	master of each repository is always working and we can deploy at any time.
* We regularly update the `wmde/fundraising-frontend-content` dependency.

## Considered Options

* Rely on `composer.lock` for pinning current `@dev` version of each bounded context
* Semantic versioning of bounded contexts

## Decision Outcome

We will use semantic versioning for 3 months (until 2020-08-14). After
that period we'll evaluate the actual benefits and drawbacks of semantic
versioning. If the drawbacks outweigh the benefits and we can't fix them
through other means (automation, CI), we'll abandon semantic
versioning and return to trunk-based dependencies.

## Pros and Cons of the Options

### Semantic versioning of bounded contexts

We create a new version tag whenever we want to use the new master of a
bounded context and update the dependencies of the FundraisingFrontend
afterwards.

We follow the following logic when deciding which part of the version to
increase:

* **Major**: 
  * All database changes
  * Changes in the public API (constructors, non-optional method parameters)
* **Minor**: Adding new methods or optional parameters to the public API
* **Patch**: Bug fixes that don't change the public API

In the FundraisingFrontend and FOC code, we will use the full version
number with the `~`  version selector instead of `^`.  This will force us
to consciously increase the minor version whenever we add new features to
the bounded contexts.

We still check in the `composer.lock` file to keep the software versions
stable when using `composer install`.

* Good, because the version number increases will show how rapidly a
  bounded context is changing
* Good, because it's easier to "roll back" to a previous version
* Good, because `composer validate` can become part of the continuous
  integration, no longer throwing `unbound version constraints (@dev)`
  errors.
* Good for situations where we need to do a bug-fix release but have
  already merged a feature - in those cases, we can backport the fix to a
  new patch version.
* Good, because tag annotations can act as change logs
* Good, because we can create bugfix releases of bounded contexts that
  omit some feature commits. This can replace the need for development
  feature flags in some cases.
* Bad, because we'll need to add releases to almost every change we make
  in bounded contexts, creating additional work - tagging the release,
  updating the version in the consuming applications.
* Bad, because it's harder to develop a feature in two parallel
  repositories: In the Application, we have to change the version of the
  bounded context to your development branch, when the feature gets
  accepted, we need to create a new release on the bounded context and
  change the dependency back to the new release number. Without safeguards
  on the CI, this process has the risk of accidentally keeping development
  branches as dependencies.

### Rely on `composer.lock` for pinning versions of bounded contexts

This is what we've been using for the last 4 years:

We keep the version specification of bounded contexts at `@dev`.

Whenver we need the changes from a bounded context for from the
`wmde/fundraising-frontend-content` repository, we do a `composer require
context-name:@dev` to update that context specifically.

We use `composer update` with caution, to avoid pulling in changes from
bounded contexts that aren't ready yet.

* Good, because it fits our development model
* Good, because it encourages developer communication
* Bad, because we can accidentally pull in unwanted changes when doing
	`composer update`.

## Links

* Versioning may become unnecessary when we integrate the Fundraising App
  and Fundraising Operation Center. See [ADR
  012](012_Code_repositories.md)

