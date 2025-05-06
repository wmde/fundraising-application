# Required PHP versions and release numbers

Date: 2025-04-23

Deciders: Abban Dunne, Corinna Hillebrand, Gabriel Birke, Tanuja D.

## Status

Accepted

## Context

We use [Semantic Versioning][1] for our libraries. Most of our code is only used
by the fundraising applications, but we also have general-purpose libraries that
other open-source projects use. We publish these libraries in the PHP package
repository [packagist.org][3].

This document contains two decisions:

1. **When should we increase the require PHP version in our projects?**
2. **When increasing the minimum required PHP version in our `composer.json`
   file, do we create a major, minor or patch release?**

The following factors affect the decision:

- The release cycle of PHP
- The dependency resolution logic of [composer][2]
- Needs of *consumers* of a library
- Needs of a *publisher* of a library

### PHP Release cycles

The regular PHP release cycle for minor versions is yearly. Each minor
release brings some new language features and some deprecations that the
next release (esp. the next major release) may remove.

As long as our code does not use the new features, we don't *need* to
require a new PHP version. If the code uses the new features, it becomes
backwards-incompatible and we *must* update the PHP version requirement in
`composer.json` to avoid breaking packages.

We *should* fix any deprecations when trying out our code with the new
version, to make our code forwards-compatible. We *must* update any
deprecations before we increase the version requirement in `composer.json`
to avoid breaking packages.

### The dependency resolution logic of composer

[composer][2], the package and dependency management software for PHP
libraries, resolves dependencies as a graph, i.e. it resolves "dependencies of
dependencies" until hit has resolved all dependencies. While resolving
dependencies, it looks at the version requirements (`require` section of
`composer.json`) of each package, using the [version constraints][4] of the
requirements. This includes the PHP runtime version dependency.

Compose has three dependency resolution behaviors relevant to the decision:

- When a higher version requirement can't be satisfied, composer will try
  to satisfy it with a lower version.
  - If it can't satisfy the dependencies with
     *any* combination of versions, it will abort `update` and `install` commands with an error message.
  - For developers who are not aware of the PHP runtime requirement checking,
    it can lead to a phenomenon of **undetected version stagnation**, where
    the `update` command succeeds but the PHP runtime is "holding back" the package at a
    lower release.
- On update, composer will determine the minimum acceptable PHP runtime by looking at the
  PHP version you are running composer with.
- Composer will write the PHP runtime version that you used during `composer update` to
  the `composer.lock` file and check the runtime environment in the auto-generated
  autoload code (see the [release notes for composer 2][5]).
  This will create warnings when you are trying to run the project in an environment that
  has a lower PHP version than the one you ran `composer update` in.

You can work around some of these behaviors with the
`--ignore-platform-reqs` flag and `platform-overrides` settings in
`composer.json`, but these workarounds should be the exception and not the
norm.

Boiled down to one sentence: If a package increases version constraints of
its *dependencies* (including the PHP runtime version), it creates an
"update pressure" for all *dependents* (i.e. packages that depend on it).

Examples:

- You are installing dependencies with PHP 8.3, composer encounters the
  dependency `"example/my-package": "~1.0"`. Its release `1.1.0` requires PHP 8.3, its
  release `1.2.0` requires PHP 8.4. `composer` will resolve this to version
  `1.1.0`. If you re-run `composer update` with PHP 8.4, composer will
  resolve it to version `1.2.0`. With minor versions, you might not notice
  that you are using an "older" version of the library and wonder why
  you can't use the new API features of version `1.2.0`.
- You are installing dependencies with PHP 8.3, composer encounters the
  dependency `"example/my-package": "~2.0"`. Its release `2.0.0` requires PHP 8.4.
  Composer will abort the update with a message that it can't satisfy the dependency
  specification.
- You have installed your project dependencies with composer and PHP 8.4,
  package the project and deploy it to a server that is running PHP 8.3.
  Running the code on the server will either fail with syntax errors 
  (because your code or *any* dependency in the dependency graph used new 
  language features) or it will issue a warning when running the autoload code.


### Needs of consumers of a library

- Have a stable dependency that adheres to [Semantic Versioning][1]:
  Updating a dependency with a minor or patch version must not break the
  code (with syntax errors because of the used language features) if the
  PHP version given by the dependency is inside the range of the version
  specification.
- Release notes should mention changed runtime requirements and/or
    compatibility fixes.
- Avoid "**update pressure**" as much as possible - if you don't need the new
    features of a new release, the release should not force you to update your
    runtime environment. Transitive dependencies (i.e. "dependencies of dependencies", etc.)
    have a higher likelihood for "update pressure".
- "**Version Churn**" - Frequent major releases can be stressful because they
    indicate backwards breaking changes. They might not affect you, but a
    major release is more expected to go wrong.

### Needs of a publisher of a library

- Ability to evolve the API, using new language features
- Gently nudge the PHP ecosystem forward by requiring newer (or at least
    supported) PHP versions.
- Grow a large user base and community to get patches and feedback,
    improving the library.
- Signal stability and reliability by having a reliable release
    schedule of not more than one major release per year (see "Version
    Churn").

### Possible release strategies

#### Only patch releases for forward-compatibility

Keep the required PHP version the same (probably 1-4 minor releases behind
the current version of PHP).

This is a strategy for projects in "maintenance mode" or development
teams that have a conservative attitude towards new PHP features.

##### Pro

- Consumers of the library will value its stability.
- By avoiding/fixing deprecated features, this library might even support multiple
  major PHP versions.

##### Con

- Developer experience and code quality might suffer, because newer PHP
  features allow for more "elegant" and "safe" development.
- You need continuous integration tooling that uses a test matrix
  with all supported PHP versions to detect breaking changes.

#### Minor releases

Increase the required PHP version, then do a minor release to
indicate a new "feature". We ensure that we don't change the public API.

This strategy fits best for projects in "maintenance mode" or where new
development team rarely adds new features.

##### Pro

- Avoids "Version Churn" that indicates lack of stability.
- With careful coding practices (avoiding new language features) you can 
  support multiple major PHP versions.
- Subtle "nudging" of the PHP ecosystem towards newer, supported versions.

##### Con

- If you use new language features in your library, the changes may break the stability
  promise of [Semantic Versioning][1], where minor versions must not break the API.
  The compatibility promise for APIs consists both of *code* compatibility and
  the *environment* compatibility.
- Can lead to "undetected version stagnation" for users on older PHP
  versions that wonder why their `composer update` does not update your
  library. If you release newer versions with features, users on older
  PHP versions might wonder why trying to use those features does not work.
- Can create "update pressure".
- If you decide to not increase the required PHP version, you need continuous
  integration tooling that uses a test matrix with all supported PHP versions
  to detect breaking changes (accidentally using incompatible new language features).

#### Major releases

Increase the required minor PHP version. Then do a major release to
signal a "major change" in backwards compatibility.

##### Pro

- Avoids the "undetected version stagnation" problem.
- Clear signal of a possible backwards breaking change.
- Makes it easier to maintain different versions of a library that target
  different PHP versions.

##### Con

- If the release contains no other changes (using new language features, adding new
    features, actually doing backwards-breaking changes), this can lead to
    "Version Churn" and loss of trust.
- Can create even more "update pressure".

## Decision

### When to upgrade to a new PHP release

We'll follow the yearly release cycle of PHP, updating at the beginning of the
year.

Decision drivers:

- We want to use the newest language features without accidentally
  breaking backwards compatibility.
- We want fast response times in our continuous integration, which means avoiding
  a test matrix for backwards compatibility.
- We want to make our maintenance a continuous effort. We think of yearly maintenance
  as less stressful for us than a "big bang" update every few years.
- We want to "gently nudge" the evolution of the PHP ecosystem.

### What versions to use for the new release

When a new PHP version comes out, we will do *patch* releases for forwards
compatibility of our code. The patch releases **will not** increase the
required PHP version.

For "internal" libraries (shared by the fundraising applications but not
outside of our projects) we will do *minor* releases to avoid "Version Churn".

For our libraries that used by other code than the fundraising
applications, we'll decide on a case-by-case basis:

- If the library does not get any new features and does not use new
  language features in its public interface, we will use a *minor*
  version.
- If we have added features, need to adapt the public API to conform with
  language requirements or if we switched to a major PHP version, we will
  use a major version. If we increase a major version of the production
  dependencies (*not* dev dependencies like PHPUnit), we might also
  increase the *major* version.

## Other sources talking about the topics

- https://sebastiandedeyne.com/composer-semver-and-underlying-dependency-changes/ 
  In Favor of minor releases, explains dependency resolution
- https://getrector.com/blog/how-to-bump-minimal-version-without-leaving-anyone-behind - in favor of keeping backwards compatibility as long as possible


[1]: https://semver.org/
[2]: https://getcomposer.org/
[3]: https://packagist.org/
[4]: https://getcomposer.org/doc/articles/versions.md
[5]: https://blog.packagist.com/composer-2-0-is-now-available/
[6]: https://www.php.net/supported-versions
