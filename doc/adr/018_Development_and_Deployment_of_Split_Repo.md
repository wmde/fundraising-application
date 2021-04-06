# Deployment of code base split into server and client side code

Date: 2021-03-10

Deciders: Gabriel Birke, Abban Dunne, Corinna Hillebrand, Conny Kawohl

Technical Story: https://phabricator.wikimedia.org/T273800

## Status

Accepted

## Context and Problem Statement

With the Fundraising App split into two repositories (see [ADR
017](./017_Split_Repositories.md)), we need to deploy two specific
branches of the repositories to test and production. We need a mechanism
or process to define which two branches should be deployed together.

## Decision Drivers 

* **Speed**: Currently, the deployment playbook builds the assets of the 
     frontend branch from scratch. If we could use pre-built assets, the deployment 
	 would be faster. 
* **Developer experience**: The app won't work as expected or represent
   the required test state if we deploy the wrong branches together. Our
  solution should prevent such an "out of sync" scenario as good as
  possible while at the same time not requiring too many manual steps from
  the developers.
* **Traceability**: We should be able to check which branches were used
	for a deployment.


## Considered Options

* Release client-side code as a NPM package (not on npm but on GitLab).
* Use Git Submodules
* Decide at deploy time, no "source of truth"

## Decision Outcome

Chosen option: "Decide at deploy time", because it's the least amount of
initial effort and the least amount of ongoing effort for each client-side change.

We accept the additional risks of making mistakes at deploy time. If we
make too many mistakes, we'll develop checks to mitigate them.

## Pros and Cons of the Options

### NPM package

We release `fundraising-app-frontend` as an npm package and add it as a
dependency to `fundraising-app`, with a post-install/post-update script
that copies the assets to `web/skins/laika`. 

To deploy a client-side branch, we would have to create a branch in
`fundraising-app` that points to the client-side branch. We must prevent this branch
from being merged into the `main` branch of `fundraising-app`.

To deploy a new release of the client-side code, we would would have to
merge the branch in `fundraising-app-frontend`, create a new release and
create a branch and pull request in `fundraising-app` that updates the release.

 The following steps could (and should) be automated:
	* Build the assets in each branch (as a separate commit) or fail the CI when the assets have not been built
	* Creating the branch in the server-side code
	* Updating the branch in the server-side code when we merge the client-side
	  code, either as an update or by deleting the feature branch and creating a new pull request with the new release.


* Good, because deployment and server-side development use prebuilt
  assets.
* Good, because we have a clear "link" designation (semantic version or branch
  name) between the two repositories.
* Bad, because the effort to build the automation is high.
* Bad, if there is no automation: Lots of potential for developer mistakes
  and repositories/deploys getting out of sync.
* Bad, because `npm install` will install all the dependencies (Vue, Vuex,
  etc.) unneccessarily, because we're only interested in the compiled
  assets.
* Bad, because of the effort to adapt our menu-based deployment script to use
  branches instead of pull requests when deploying to test.

### Git Submodules

`fundraising-app-frontend` is a submodule of `fundraising-app`. We use the
Makefile for `fundraising-app` build the assets when needed.

The workflow for creating branches for deploying to test would be the same
as the one with npm: The new `fundraising-app` branch would contain a
branch designation in the `.gitmodules` file.

The workflow for creating a new production release would be to merge the
branch in `fundraising-app-frontend` into `main` and creating a new pull
request in `fundraising-app` that updates the submodule commit id.

* Good, because we don't need to change much in our build and deploy
  setup.
* Good, because we have less risk of deploying a stale version
* Bad, because we can't differentiate between test and production releases
  and have no clear version number, only commit IDs
* Bad, because we have to pull in the development dependencies of
  `fundraising-app-frontend`.
* Bad, because we have to build the assets on deployment on deployment.
* Bad, because using submodules could make the automation of branch
  creation and update more complicated.

### Decide at deploy time

We pre-build the assets in the CI piepline of `fundraising-frontend-app`.
Each branch will have pre-built assets.

We add another variable (e.g. `FRONTEND_DEPLOY_BRANCH`) to the deployment
playbook that downloads the pre-built assets for that variable when
deploying. 

The deploy playbook should write a file on the server where we can look up
the version and/or commit IDs (or branch names) of both repositories, so
we can determine which versions are on the server.

* Good, because we don't need much effort for automating synchronized
  releases.
* Good, because it speeds up the deployment. The `npm install` and `npm
	build` steps are "outsourced" to the CD pipeline of `fundraising-app-frontend`.
* Bad, because we can't check if the changes to both repositories are
	compatible at review time, only when it's deployed to test.


