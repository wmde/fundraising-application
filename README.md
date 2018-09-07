[![Build Status](https://travis-ci.org/wmde/FundraisingFrontend.svg?branch=master)](https://travis-ci.org/wmde/FundraisingFrontend)
[![Code Coverage](https://scrutinizer-ci.com/g/wmde/FundraisingFrontend/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/wmde/FundraisingFrontend/?branch=master)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/wmde/FundraisingFrontend/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/wmde/FundraisingFrontend/?branch=master)

User facing application for the [Wikimedia Deutschland](https://wikimedia.de) fundraising.

<!-- toc -->

* [Installation](#installation)
* [Running the application](#running-the-application)
* [Configuration](#configuration)
* [Running the tests](#running-the-tests)
* [Emails](#emails)
* [Database](#database)
* [Frontend development](#frontend-development)
* [Skins](#skins)
* [Updating the dependencies](#updating-the-dependencies)
* [Deployment](#deployment)
* [Profiling](#profiling)
* [Project structure](#project-structure)
* [See also](#see-also)

<!-- tocstop -->

## Installation

For development you need to have Docker and docker-compose installed. You need at least Docker Version >= 17.09.0 and docker-compose version >= 1.17.0. If your OS does not come with the right version, please use the official installation instructions for [Docker](https://docs.docker.com/install/) and [docker-compose](https://docs.docker.com/compose/install/). You don't need to install other dependencies (PHP, Node.js, MySQL) on your machine.

Get a clone of our git repository and then run:

    make setup

This will
 
- Install PHP and Node.js dependencies
- Copy a basic configuration file. See section [Configuration](#configuration) for more details on the configuration.
- (Re-)Create the database structure and generate the Doctrine Proxy classes
- Build the assets & JavaScript

## Running the application

    docker-compose up

The application can now be reached at http://localhost:8082/index.php, debug info will be shown in your CLI.

## Configuration

### Test configuration

To speed up the tests when running them locally, use SQLite instead of the default MySQL. This can be done by
adding the file `app/config/config.test.local.json` with the following content:

```json
{
    "db": {
        "driver": "pdo_sqlite",
        "memory": true
    }
}
```

### Payments

For a fully working instance with all payment types and working templates you need to fill out the following
configuration data:

    "operator-email"
    "operator-displayname-organization"
    "operator-displayname-suborganization"
    "paypal-donation"
    "paypal-membership"
    "creditcard"

### Content

The application needs a copy of the content repository at https://github.com/wmde/fundraising-frontend-content to work properly. 
In development the content repository is a composer dev-dependency. If you *want* to put the content repository in another place, you need to configure the `i18n-base-path` to point to it.
The following example shows the configuration when the content repository is at the same level as the application directory:

    "i18n-base-path": "../fundraising-frontend-content/i18n"

## Running the tests

### Full CI run

    make ci

If you want to run all the CI tasks in parallel, without ticking progress bars from commands that support it, run the following command instead:

    make -j --output-sync ci DOCKER_FLAGS="" 


### For tests only

    make test
    docker run -it --rm --user $(id -u):$(id -g) -v $(pwd):/app -w /app node:8 npm run test

### For style checks only

    make cs
    docker run -it --rm --user $(id -u):$(id -g) -v $(pwd):/app -w /app node:8 npm run cs

For one context only

    make phpunit TEST_DIR=contexts/PaymentContext

### phpstan

Static code analysis is performed via [phpstan](https://github.com/phpstan/phpstan/) during runs of `make ci`.

In the absence of dev-dependencies (i.e. to simulate the vendor/ code on production) it can be done via

    docker build -t wmde/fundraising-frontend-phpstan build/phpstan
    docker run -v $PWD:/app --rm wmde/fundraising-frontend-phpstan analyse -c phpstan.neon --level 1 --no-progress cli/ contexts/ src/

These tasks are also performed during the [travis](.travis.yml) runs.

## Emails

All emails sent by the application can be inspected via [mailhog](https://github.com/mailhog/MailHog)
at [http://localhost:8025/](http://localhost:8025/)

## Database

### Database migrations

Out of the box, the database should be in a usable state for local development. For production changes to the database,
doctrine migration scripts may be used. Migration scripts are stored in the *migrations* directory of
the [FundraisingStore repository](https://github.com/wmde/FundraisingStore/tree/master/migrations).

The scripts from the FundraisingStore repository can be executed through FundraisingFrontend by running `make migration` commands.

To execute a specific script, run the following command and add the version number of the migration script you want to use.
As an example, executing `migrations/Version20180612000000.php` would look like this:

```
make migration-execute MIGRATION_VERSION=20180612000000
```

You can also revert a script (if implemented) through an equivalent `migration-revert` command:

```
make migration-revert MIGRATION_VERSION=20180612000000
```

Note that Doctrine creates its own `doctrine_migration_versions` table where it stores the status of individual migrations.
If you run into issues and want to reset the state of a migrations it's best to check that table directly or use the `versions`
command from doctrine-migrations which supports `--add` and `--delete` paramters:

```
vendor/wmde/fundraising-store/vendor/doctrine/migrations/bin/doctrine-migrations migrations:version
```

All of the previous make commands are only available in a dockerized environment.
If you intend to use these commands on servers, you can check the `Makefile` for the underlying commands.

### Accessing the database from a Docker image

The database container of the Docker development environment is not exposed to the outside. If you want to connect to 
the default fundraising frontend database using the MySQL command line client, you need first to find out the Docker 
network name where the database is running in. With the command

    docker network ls

you can list all networks. There will be one network name ending in `fundraising_proxy` (the prefix is probably the 
directory name where you checked out this repository).

Next up is finding out the full name of the database container with the command

    docker ps

The database container will be the one ending in `_database_1`. The prefix is probably the directory name where you checked out this repository.

Copy the full network name and container name and use them instead of the placeholders `__CONTAINER_NAME__` and 
`__NETWORK_NAME__` in the following command to run the MySQL command line client:  

    docker run -it --link __CONTAINER_NAME__:mysql --net __NETWORK_NAME__ --rm mysql:5.6 \
        sh -c 'exec mysql -h database -P 3306 -u fundraising -p"INSECURE PASSWORD" fundraising'

To use PHPMyAdmin, use the following command to run it on port 8089:

	docker run -it --link __CONTAINER_NAME__:db --net __NETWORK_NAME__ -p 8099:80 phpmyadmin/phpmyadmin

### Accessing the database from your host machine

If you want to expose the port of the database on your guest host (localhost), for example for using a GUI client, 
you need to create an "override" file for `docker-compose.yml`. Example file, called `docker-compose.db.yml`:

```yaml 
version: '3'

services:
    database:
        ports:
            - "3306:3306"
``` 

You then start the environment with the following command:

    docker-compose -f docker-compose.yml -f docker-compose.db.yml up

You will be prompted for a password which you can grab from `config/config.prod.json`.

## Frontend development

For a full JS CI run

    make ci

If JavaScript files where changed, you will need to (re)run

    make js

If you want to debug problems in the Redux data flow, use the command

    make js REDUX_LOG=on

Actions and their resulting state will be logged.
    
### Automatic recompilation of assets during development

If you are working on the JavaScript files and need automatic recompilation when a files changes, 
you can run the following Docker commands 

Run the Docker command corresponding to the skin:

    docker run --rm -it -u $(id -u):$(id -g) -v $(pwd):/app -v $(pwd)/web/skins/cat17:/app/skins/cat17/web -w /app/skins/cat17 -e NO_UPDATE_NOTIFIER=1 node:8 npm run watch
    docker run --rm -it -u $(id -u):$(id -g) -v $(pwd):/app -v $(pwd)/web/skins/10h16:/app/skins/10h16/web -w /app/skins/10h16 -e NO_UPDATE_NOTIFIER=1 node:8 npm run watch 

If you want to debug problems in the Redux data flow add the parameter `-e REDUX_LOG=on` to the command line above

Actions and their resulting state will be logged.

Until [issue T192906](https://phabricator.wikimedia.org/T192906) is fixed, the commands `make js` and `make ui` will 
issue (harmless) error messages as long as the symlinks are in place. 

## Skins

If skin assets where changed, you will need to run

    make ui

## Updating the dependencies

To update all the PHP dependencies, run

    make update-php

For updating an individual package, use the command line

    docker run --rm -it -v $(pwd):/app -v ~/.composer:/composer -u $(id -u):$(id -g) composer update --ignore-platform-reqs PACKAGE_NAME

and replace the `PACKAGE_NAME` placeholder with the name of your package.

To update the skins, run

    make update-js 

## Deployment

For an in-depth documentation how deployment on a server is done, 
see [the deployment documentation](deployment/README.md).

## Profiling

This is not working at the moment.

(When accessing the API via `web/index.dev.php`, profiling information will be generated and in
`app/cache/profiler`. You can access the profiler UI via `index.dev.php/_profiler`.)

## Project structure

This app and its used Bounded Contexts follow the architecture rules outlined in [Clean Architecture + Bounded Contexts](https://www.entropywins.wtf/blog/2018/08/14/clean-architecture-bounded-contexts/).

![Architecture diagram](https://user-images.githubusercontent.com/146040/44942179-6bd68080-adac-11e8-9506-179a9470113b.png)

Used Bounded Contexts:

* [Donation Context](https://github.com/wmde/fundraising-donations)
* [Membership Context](https://github.com/wmde/fundraising-memberships)
* [Payment Context](https://github.com/wmde/fundraising-payments)
* [Subscription Context](https://github.com/wmde/fundraising-subscriptions)

### Production code layout

* `src/`: framework agnostic code not belonging to any Bounded Context
	* `Factories/`: application factories used by the framework, including top level factory `FFFactory`
	* `Presentation/`: presentation code, including the `Presenters/`
	* `Validation/`: validation code
* `vendor/wmde/$ContextName/src/` framework agnostic code belonging to a specific Bounded Context
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
in `src/` can be found in `tests/`.

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
		<th>Network</th>
		<th>Framework (Silex)</th>
		<th>Top level factory</th>
		<th>Database and disk</th>
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
		<td>No</td>
		<td>No</td>
		<td>Discouraged</td>
		<td>Yes</td>
	</tr>
	<tr>
		<th>EdgeToEdge</th>
		<td>No</td>
		<td>Yes</td>
		<td>Yes</td>
		<td>Yes</td>
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
* `build/`: Configuration and Dockerfiles for the development environment and Travis CI

## See also

* [Rewriting the Wikimedia Deutschland fundraising](https://www.entropywins.wtf/blog/2016/11/24/rewriting-the-wikimedia-deutschland-funrdraising/) - blog post on why we created this codebase
* [Implementing the Clean Architecture](https://www.entropywins.wtf/blog/2016/11/24/implementing-the-clean-architecture/) - blog post on the architecture of this application
