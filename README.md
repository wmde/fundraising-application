[![Build Status](https://travis-ci.org/wmde/FundraisingFrontend.svg?branch=master)](https://travis-ci.org/wmde/FundraisingFrontend)
[![Code Coverage](https://scrutinizer-ci.com/g/wmde/FundraisingFrontend/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/wmde/FundraisingFrontend/?branch=master)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/wmde/FundraisingFrontend/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/wmde/FundraisingFrontend/?branch=master)

User facing application for the [Wikimedia Deutschland](https://wikimedia.de) fundraising.

The easiest way to get a working installation of the application is to use [Vagrant](https://www.vagrantup.com/).
Just get a clone of our git repository and run `vagrant up` in it. Then `vagrant ssh` into it and go to `/vagrant`, where you will be able to run the full test suite. (Excluding a handful of payment provider system tests).


## Installation

### Using Vagrant

Get a copy of the code and make sure you have [Vagrant](https://www.vagrantup.com/) installed.

Inside the root directory of the project, execute

    vagrant up
    vagrant ssh

Once you're ssh'd into the VM, you can find the application installed in `/vagrant`.
At this point you will be able to run nearly all of the tests (those that you cannot run will be
skipped automatically). To run the system tests and get the app to fully show in your browser,
you will need to do additional configuration, as per the configuration section.

### Local Installation

System dependencies:

* PHP >= 7
* php7.0-intl
* php7.0-curl
* php7.0-sqlite3 (only needed for running the tests)
* Node.js and npm (only needed in development for compiling the JavaScript and running the JavaScript tests)
* [kontocheck extension](http://kontocheck.sourceforge.net/) (only needed when you want to use or test direct debit)

Get a clone of our git repository and then run these commands in it:

	composer install
	npm install
	npm run build-js

For the database connection you need to create the file `app/config/config.prod.json` and enter your database
connection data. If you're using MySQL, [it's important](http://stackoverflow.com/questions/5391045/how-to-define-the-use-of-utf-8-in-doctrine-2-in-zend-framework-application-ini) to add the encoding to the `driverOptions` key.

	"db": {
		"driver": "pdo_mysql",
		"user": "donations_user",
		"password": "s00pa_s33cr1t",
		"dbname": "all_donations",
		"host": "localhost",
		"charset": "utf8",
		"driverOptions": {
			"1002": "SET NAMES utf8"
		}
 	}

## Configuration

For a fully working instance with all payment types and working templates you'll also need to fill out the following
configuration data:

	 - `cms-wiki-url`
	 - `bank-data-file`
	 - `cms-wiki-api-url`
	 - `cms-wiki-user`
	 - `cms-wiki-password`
	 - `cms-wiki-title-prefix`
	 - `operator-email`
	 - `operator-displayname-organization`
	 - `operator-displayname-suborganization`
	 - `paypal`
	 - `creditcard`

## Running the application

For development

	cd web
	php -S 0:8000

The "add donation" form can then be found at http://localhost:8000/index.php

## Running the tests

**Full CI run**

    composer ci

For tests only

    composer test ; npm run test

For style checks only

	composer cs ; npm run cs
    
**PHP**

For tests only

    composer test
    
For x (unit/integration/edgetoedge) tests only

    vendor/bin/phpunit --testsuite=x

For one context only

    vendor/bin/phpunit contexts/DonationContext/

**JS**

For a full JS CI run

	npm run ci

If JavaScript files where changed, you will first need to run

    npm run build-js

If you are working on the JavaScript files and need automatic recompilation when a files changes, then run

    npm run watch-js

If you want to debug problems in the Redux data flow, set the following variable in the shell environment:

    export REDUX_LOG=on

Actions and their resulting state will be logged.

## Deployment
For an in-depth documentation how deployment on a server is done, 
see [the deployment documentation](deployment/README.md).

## Profiling

When accessing the API via `web/index.dev.php`, profiling information will be generated and in
`app/cache/profiler`. You can access the profiler UI via `index.dev.php/_profiler`.

## Project structure

This codebase follows a modified version of [The Clean Architecture](https://8thlight.com/blog/uncle-bob/2012/08/13/the-clean-architecture.html),
combined with a partial application of [Domain Driven Design](https://en.wikipedia.org/wiki/Domain-driven_design).
The high level structure is represented by [this diagram](https://commons.wikimedia.org/wiki/File:Clean_Architecture_%2B_DDD,_full_application.svg).

### Production code layout

* `src/`: framework agnostic code not belonging to any Bounded Context
	* `Factories/`: application factories used by the framework, including top level factory `FFFactory`
	* `Presentation/`: presentation code, including the `Presenters/`
	* `Validation/`: validation code
* `contexts/$ContextName/src/` framework agnostic code belonging to a specific Bounded Context
	* `Domain/`: domain model and domain services
	* `UseCases/`: one directory per use case
	* `DataAccess/`: implementations of services that binds to database, network, etc
	* `Infrastructure/`: implementations of services binding to cross cutting concerns, ie logging
* `web/`: web accessible code
	* `index.php`: production entry point
* `app/`: contains configuration and all framework (Silex) dependent code
	* `bootstrap.php`: framework application bootstrap (used by System tests)
	* `routes.php`: defines the routes and their handlers
	* `RouteHandlers/`: route handlers that get benefit from having their own class are placed here
	* `config/`: configuration files
		* `config.dist.json`: default configuration
		* `config.test.json`: configuration used by integration and system tests (gets merged into default config)
		* `config.test.local.json`:  instance specific (gitignored) test config (gets merged into config.test.json)
		* `config.prod.json`: instance specific (gitignored) production configuration (gets merged into default config)
	* `js/lib`: Javascript modules, will be compiled into one file for the frontend.
	* `js/test`: Unit tests for the JavaScript modules
* `var/`: Ephemeral application data
    * `log/`: Log files (in debug mode, every request creates a log file)
    * `cache/`: Cache directory for Twig templates

### Test code layout

The test directory structure (and namespace structure) mirrors the production code. Tests for code
in `src/` can be found in `tests/`. Tests for code in `contexts/$ContextName/src/` can be found in
`contexts/$ContextName/tests/`.

Tests are categorized by their type. To run only tests of a given type, you can use one of the
testsuites defined in `phpunit.xml.dist`.

* `Unit/`: small isolated tests (one class or a small number of related classes)
* `Integration/`: tests combining several units
* `EdgeToEdge/`: edge-to-edge tests (fake HTTP requests to the framework)
* `System/`: tests involving outside systems (ie, beyond our PHP app and database)
* `Fixtures/`: test doubles (stubs, spies and mocks)

If you need access to the application in your non-unit tests, for instance to interact with
persistence, you should use `TestEnvironment` defined in `tests/TestEnvironment.php`.

#### Test type restrictions

<table>
	<tr>
		<th></th>
		<th>Database (in memory)</th>
		<th>Top level factory</th>
		<th>Framework (Silex)</th>
		<th>Network & Disk</th>
	</tr>
	<tr>
		<th>Unit</th>
		<td>No</td>
		<td>No</td>
		<td>No</td>
		<td>No</td>
	</tr>
	<tr>
		<th>Integration</th>
		<td>If needed</td>
		<td>Discouraged</td>
		<td>No</td>
		<td>Read only</td>
	</tr>
	<tr>
		<th>EdgeToEdge</th>
		<td>Yes</td>
		<td>Yes</td>
		<td>Yes</td>
		<td>Read only</td>
	</tr>
	<tr>
		<th>System</th>
		<td>Yes</td>
		<td>Yes</td>
		<td>Yes</td>
		<td>Yes</td>
	</tr>
</table>

### Other directories

* `deployment/`: Ansible scripts and configuration for deploying the application


