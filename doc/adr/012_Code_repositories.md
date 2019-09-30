# Code Repositories

Date: 2019-09-27

## Status
Accepted

## Context

Before the rewrite of the Fundraising Application both the Fundraising Application and the Fundraising Operation Center depended on the FundraisingStore library that contained all database-related code for the whole fundraising domain. The rewrite introduced bounded contexts, that, in theory, should encapsulate database access. In practice, the bounded contexts currently depend on FundraisingStore, which in hindsight turned out to be a bad idea, since it creates a domain-level dependency that crosses the separate domains, violating the Single Responsibility Principle.

The long-term goal of the dev team is to move the Fundraising Operation Center to the same domain-driven clean architecture like the Fundraising Frontend, with both applications sharing the bounded contexts. 

The code quality of the Fundraising Operation Center currently is not high enough to be in the same Git repository and on the same "level" (directory-wise) as the Fundraising Frontend.

The I18N data is also in a separate repository, with a separate deploy cycle - we have a Jenkins instance that immediately deploys changes in the I18N data to test and production, as opposed to code deploys which happen manually. We chose this deploy mode to enable the Fundraising department to change text in the Fundraising Application themselves, without developer intervention. This leads to problems where we develop a new feature and need to change the i18n data:

* The necessary I18N changes for a feature might break the existing application. 
* The dev environment has the I18N repository as a dev dependency, leading to frequent but unnecessary package updates.

## Decisions

* To enable the Fundraising Operation Center to use the bounded contexts instead of the FundraisingStore, we split the bounded contexts into separate Git repositories (PHP packages) and add them as dependencies to the Fundraising Operation Center.
* We will move the functionality from FundraisingStore into the bounded contexts ([Ticket T232010](https://phabricator.wikimedia.org/T232010)). When all the functionality has been moved, we will remove the FundraisingStore as a dependency from the bounded contexts and the Fundraising Operation Center.
* When the code quality of the Fundraising Operation Center has become acceptable, we will merge the Fundraising Application, the Fundraising Operation Center and the bounded contexts into one git repository.
* Improving the situation with the I18N data repository and deployment will be discussed in a separate ADR. 

## Consequences

Splitting the bounded contexts into different packages has the following drawbacks:

* The code search feature of the IDE does not find classes from dependencies by default. This makes it harder to navigate the code.
* To work on a feature we need to have several project windows open in the IDE.
* We have to keep the dependencies (PHP version, CI tool versions) of the bounded contexts in sync and maintain their CI pipelines. 
* Refactoring class names in bounded contexts leads to errors in the Fundraising Application and the Fundraising Operation Center, where we must make the changes manually.
* Due to the common dependency on FundraisingStore all database schema changes lead to an "update cascade" through all bounded contexts, the Fundraising Operation Center and the Fundraising Application - 6 repositories in total.

We accept those drawbacks as necessary on our way to a more maintainable code base. Removing the FundraisingStore is the highest priority.