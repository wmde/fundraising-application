# Code Repositories on GitHub

Date: 2020-04-03

## Status
Accepted

## Context

The fundraising software is split into several Git repositories, hosted on GitHub: Applications (Fundraising Application and Fundraising Operation Center), Bounded Contexts (containing the business logic of the applications), I18N data. 

### Bounded contexts split into Git repositories

During the rewrite of the Fundraising Application we introduced the concept of "[Bounded Contexts](https://codeburst.io/ddd-strategic-patterns-how-to-define-bounded-contexts-2dc70927976e)". To better enforce the architectural separation of the bounded contexts, we put them in separate GitHub repositories. For some time that independence was only surface-level deep because all the contexts depended on the same database "abstraction" library (FundraisingStore), but that is now fixed.

#### Benefits of Bounded Contexts in separate Git repositories
* Business logic and presentation/ingress layer (web, command line, etc.) are clearly separated. 
* It's nearly impossible to introduce dependencies between the bounded contexts, as that would cause circular dependency warnings.
* When working on the bounded contexts, the CI runs faster, since the CI tests only the business logic of the bounded context and not the whole integrated application.
* Features and changes can be worked on independently from the Application - as long as the Applications dependencies are not updated, the changes are not integrated.   

#### Drawbacks of Bounded Contexts in separate Git repositories

* The code search feature of the IDE does not find classes from dependencies by default. This makes it harder to navigate the code.
* To work on a feature we need to have several project windows open in the IDE.
* We have to keep the dependencies (PHP version, CI tool versions, coding style conventions) of the bounded contexts in sync and maintain their CI pipelines. 
* Refactoring class names in bounded contexts leads to errors in the Fundraising Application and the Fundraising Operation Center, where we must make the changes manually.
* It's too easy to accidentally integrate a change from a bounded context by updating the dependencies. 

### I18N data in a Git repository

The I18N data of the Fundraising Application is in a separate repository on GitHub. It has a separate, automated deploy cycle - a Jenkins instance immediately deploys changes in the I18N data to test and production, as opposed to code deploys which happen manually. We chose this deploy mode to enable the Fundraising department to change text in the Fundraising Application themselves, without developer intervention. This leads to problems where we develop a new feature and need to change the i18n data:

* The necessary I18N changes for a feature might break the existing application. 
* The dev environment has the I18N repository as a dev dependency, leading to frequent but unnecessary package updates.

### Separation of Fundraising Application and Fundraising Operation Center in different GitHub repositories

Historically, the Fundraising Application and Fundraising Operation Center were two different code bases of varying quality. There is duplication between the code bases, with refactoring efforts to move more and more business logic into the bounded contexts which both applications share. But even with shared bounded contexts, both applications have to be deployed in sync whenever the data model changes.

The code quality of the Fundraising Operation Center currently is not high enough to be in the same Git repository and on the same "level" (directory-wise) as the Fundraising Frontend, it would break too many CI checks. The long-term goal of the dev team is to move the Fundraising Operation Center to the same domain-driven clean architecture like the Fundraising Frontend.


## Decisions
* When the code quality of the Fundraising Operation Center has become acceptable, we will merge the Fundraising Application, the Fundraising Operation Center and the bounded contexts into one Git repository.    
* We will keep the benefits of the separated bounded contexts with the following measures:
  * Investigate & discuss if the different parts will be separated just with by directory structures and different test suites for PHPUnit or if we want still want [separate composer packages inside a monorepo](https://medium.com/sroze/managing-monolithic-repositories-with-composers-path-repository-c28af031746d).
  * We will make architecture checks part of our CI. We will evaluate the tools [dephpend](https://github.com/mihaeu/dephpend), [deptrac](https://github.com/sensiolabs-de/deptrac) and [phparch](https://github.com/j6s/phparch) to see which one(s) fit our requirements best.
  * New features will be developed more strictly with [Feature toggles](https://en.wikipedia.org/wiki/Feature_toggle) to keep the ability to deploy small changes and bug fixes at any point.
* Improving the situation with the I18N data repository and deployment will be discussed in a separate ADR.  

## Consequences

* With all the source code in one repository there will be less overhead to keep the different parts of the applications in sync.
* We will make fewer mistakes when integrating bounded contexts in the application.
* We will be refactoring more efficiently
* The overall architecture of the code will be more visible from the directory structure.
* We will use the "proper" tools for checking our architecture instead on relying on infrastructure.
