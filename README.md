[![Build Status](https://travis-ci.org/wmde/FundraisingFrontend.svg?branch=master)](https://travis-ci.org/wmde/FundraisingFrontend)

User facing application for the WMDE fundraising.

## System dependencies

* PHP >= 7
* php7.0-intl
* php7.0-sqlite3 (only needed for running the tests)
* Node.js and npm (only needed in development for compiling the JavaScript and running the JavaScript tests)

## Running the application

For development

	cd web
	php -S 0:8000

## Running the tests

For tests only

    composer test

For style checks only

	composer cs

For a full CI run (including JavaScript CI)

	composer ci

To run the tests with your globally installed PHPUnit, you will need a recent version of
PHPUnit. Consult the `require-dev` section of `composer.json` for up to date information.

The JavaScript tests are run as npm scripts that mirror composer scripts:

    npm run test
    npm run cs
    npm run ci

Note that these scripts need some node.js packages installed (see below).

## Compiling the JavaScript

As a first step you must install the required node.js packages with npm:

    npm install

To compile the Javascript to be uased by the web application, run

    npm run build-js
    
If you are working on the JavaScript files and need automatic recompilation when a files changes, use

    npm run watch-js

instead. 

If you want to debug problems in the Redux data flow, set the following variable in the shell environment:
  
    export REDUX_LOG=on

Actions and their resulting state will be logged. 

## Profiling

When accessing the API via `web/index.dev.php`, profiling information will be generated and in
`app/cache/profiler`. You can access the profiler UI via `index.dev.php/_profiler`.

## Internal structure

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
* `src/`: contains framework agnostic code
	* `DataAccess/`: persistence other data access (ie network) service implementations
	* `Domain/`: application independent code belonging to the fundraising frontend bounded context
	* `Factories/`: application factories used by the framework, including top level factory `FFFactory`
	* `Infrastructure/`: services belonging to supporting domains
	* `Presentation/`: presentation code, including the `Presenters/`
	* `ResponseModel/`: common code for the response models of the use cases
	* `UseCases/`: one directory per use case
	* `Validation/`: validation code
	* All dependencies are explicitly defined in `composer.json` (including those shared with Silex)
* `tests/`: tests mirror the directory and namespace structure of the production code
	* `Unit/`: small isolated tests (one class or a small number of related classes)
	* `Integration/`: tests combining several units
	* `System/`: edge-to-edge tests
	* `Fixtures/`: test stubs and spies
	* `TestEnvironment.php`: encapsulates application setup for integration and system tests
* `var/`: Ephemeral application data
    * `logs`: Log files (in debug mode, every request creates a log file)
    * `cache`: Cache directory for Twig templates

## Test type restrictions

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
		<td>Yes</td>
		<td>If needed</td>
		<td>No</td>
		<td>No</td>
	</tr>
	<tr>
		<th>System (edge-to-edge)</th>
		<td>Yes</td>
		<td>Yes</td>
		<td>Yes</td>
		<td>No</td>
	</tr>
	<tr>
		<th>System (full)</th>
		<td>Yes</td>
		<td>Yes</td>
		<td>Yes</td>
		<td>Yes</td>
	</tr>
</table>
