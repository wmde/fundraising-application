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
	* `config/config.test.local.json`:  instance specific (gitignored) test config (gets merged into config.test.json)
	* `config/config.prod.json`: instance specific (gitignored) production configuration (gets merged into default config)
	* `js/lib`: Javascript modules, will be compiled into one file for the frontend.
	* `js/test`: Unit tests for the JavaScript modules
* `src/`: contains framework agnostic code
	* `FFFactory.php`: top level factory and service locator (used by Integration tests)
	* `UseCases/`: one directory per use case
	* All dependencies are explicitly defined in `composer.json` (including those shared with Silex)
* `tests/`: tests mirror the directory and namespace structure of the production code
	* `Unit/`: small isolated tests (one class or a small number of related classes)
	* `Integration/`: tests combining several units
	* `System/`: edge-to-edge tests
	* `TestEnvironment.php`: encapsulates application setup for integration and system tests
	* `Fixtures/`: test stubs and spies

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
