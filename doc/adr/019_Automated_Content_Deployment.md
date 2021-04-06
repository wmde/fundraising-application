# Automated Content Deployment

Date: 2021-03-12

Deciders: Abban Dunne, Corinna Hillebrand, Gabriel Birke

Technical Story: https://phabricator.wikimedia.org/T262704

## Status

Accepted

## Context and Problem Statement

We want to automatically deploy the fundraising content repository
whenever there is a change (i.e. a Git commit on the `test` or `production`
branch) to the destination servers associated with the branch.


## Decision Drivers

* For security reasons, we want to host the system that does the
  deployment on our own infrastructure.
* The repository consists of several files. We want the deployment to be
  *atomic*, i.e our application either uses the new set of files or the
  old one, but never a mix.
* We have three production servers that need to receive the new version
  simultaneously (although not atomically).
* We would like the system to notify us (via email) when a deployment
  failed.  We would like to have a record (e.g. log file) of successful
  deployments.  We have an Ansible playbook that does the atomic
  deployment to the test and production servers.
* We have a `Dockerfile` definition that packages Ansible, the deployment
  playbooks and our server configuration into a standalone Docker image.
  The deployment software can run this Docker image to do the deployment.
* We prefer "configuration as code" over setting up workflows in a GUI.
* The maintenance and onboarding effort for the system should be as low 
  as possible.
* (Optional) It would be nice if we had a "button" to trigger the
  deployment
* (Optional) It would be nice if feature branches of the application
  repository could be associated with feature branches in the content
  repository (instead of using `test`).

## Considered Options

* Cronjob and `git pull`
* GitLab Pipelines
* Self-Hosted Jenkins
* Drone CI
* Ansible web GUI
* Custom webhook script

## Decision Outcome

Chosen option: "Drone CI", because the benefits outweigh the drawbacks (see below).

## Pros and Cons of the Options

### Cronjob and `git pull`
We dismissed this option because it does not meet the requirement for
atomic deploys.

### GitLab Pipelines

We dismissed this option because it does not meet the requirement to keep the
credentials (SSH keys) for our infrastructure on premises. 

### Self-Hosted Jenkins

We had automated the content deployment with this, but the setup was too
maintenance-intensive and, especially with code-as-configuration, too
complex. When we switched infrastructure providers, we abandoned the
solution because setting it up again would have been too much effort.

### Drone CI

We created a Drone CI setup that does the automated deployment. At the
point of this decision, the setup is in prototype stage and needs some
security tweaks and documentation.

The system has two webhooks:

- for the "Ansible Runner" repository that contains the Dockerfile setup
- for the content repository, using the Docker image with the "Ansible
  Runner" to deploy the content. 

Pros and cons:
* Good, because we're using standard software that's under active
  development.
* Good, because Drone CI fits well with our Docker-based approach.
* Good, because we have a working prototype.
* Good, because we could reuse it for other deployments in the future.
* Good, because it's a learning opportunity.
* Bad, because it does too much - it's a whole CI system and we need only
  a small portion. This creates onboarding (YAML syntax), setup and
  maintenance effort.
* Bad, because the company Harness bought the project and it might linger
  or change focus/direction, even as an open source project.

### Ansible web GUI

With our Ansible-based deployment, a CI/CD system with its own
configuration language is the wrong tool for the job, we need a "http
trigger" for Ansible playbooks. There are software systems which do this:

- [Ansible AWX](https://github.com/ansible/awx)
- [Rundeck](https://www.rundeck.com/ansible)
- [Semaphore](https://ansible-semaphore.com/)
- [Polemarch](https://github.com/vstconsulting/polemarch)
- [nci-ansible-gui](https://github.com/node-ci/nci-ansible-ui)

* Good, because we're using the right tool for the job
* Good, because we can reuse it for other deployments
* Bad, because of the added complexity of the additional functionality of
  those products.
* Bad, because of the effort required to pick the right product and
  configure it.

### Custom webhook script

The script would expose an HTTP endpoint that does the following steps:

- Verify the GitHub authentication token.
- Parse the webhook request and check if the repository and branch matches
  a configured deploy destination.
- Pull the newest version of the "Ansible Runner" image
- Run the "Ansible Runner" image to deploy, parameterized by the branch.
- (Optional) Rate limit the requests (e.g. max 1 per minute) to avoid abuse.

We would automatically build the ansible runner via GitLab.

We would add another step to the deploy playbook that involves
notification on failure.

We can do the veryfication and parsing of the request with
https://packagist.org/packages/swop/github-webhook

Open question: How to communicate with Docker? We can't use the Docker
cli, because we don't want to allow the web server to access it (for
security reasons). We'd have to investigate PHP libraries that communicate
with Docker of an encrypted TCP socket, using a certificate.

* Good, because low maintenance effort
* Good, because low onboarding effort and complexity
* Bad, because unforeseen risks could occur during development
