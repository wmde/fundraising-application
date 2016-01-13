[![Build Status](https://travis-ci.org/wmde/FundraisingFrontend.svg?branch=master)](https://travis-ci.org/wmde/FundraisingFrontend)

User facing application for the WMDE fundraising.

## System dependencies

* PHP >= 7
* php5-sqlite (only needed for running the tests)

## Running the application

For development

	cd web
	php -S 0:8000

## Running the tests

For tests only

    composer test

For style checks only

	composer cs

For a full CI run

	composer ci

## Profiling

When accessing the API via `web/index.dev.php`, profiling information will be generated and in
`app/cache/profiler`. You can access the profiler UI via `index.dev.php/_profiler`.

## Internal structure

* `web/`: web accessible code
	* `index.php`: production entry point
* `app/`: contains configuration and all framework (Silex) dependent code
	* `bootstrap.php`: framework application bootstrap (used by System tests)
	* `routes.php`: defines the routes and their handlers
	* `config/config.dist.json`: default configuration
	* `config/config.test.json`: configuration used by integration and system tests (gets merged into default config)
	* `config/config.prod.json`: production configuration (gets merged into default config)
* `src/`: contains framework agnostic code
	* `FFFactory.php`: top level factory and service locator (used by Integration tests)
	* `UseCases/`: one directory per use case
	* All dependencies are explicitly defined in `composer.json` (including those shared with Silex)
* `tests/`: tests mirror the directory and namespace structure of the production code
	* `Unit/`: small isolated tests (cannot access Silex, application state or top level factory)
	* `Integration/`: tests combining several units (cannot access Silex)
	* `System/`: edge-to-edge tests
	* `TestEnvironment.php`: encapsulates application setup for integration and system tests
	* `Fixtures/`: test stubs and spies
