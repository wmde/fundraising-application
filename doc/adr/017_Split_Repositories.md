# Splitting repositories 

Date: 2021-03-01

Deciders: Gabriel Birke, Corinna Hillebrandt, Abban Dunne, Conny Kawohl

Technical Story: https://phabricator.wikimedia.org/T273800

## Status

Accepted

## Context and Problem Statement

Currently, both the client-side code (JavaScript and SCSS) and the
server-side code are in the same repository. This leads to long build
times and unneeccessary CI runs if either of the code parts change.

Also, having both "parts" in the same repository makes it harder to talk
about each part, because we used the terminology of the Fundraising
Department, which called the Fundraising Application "Frontend" and the
Fundraising Operation Center "Backend". For us as developers, "Frontend"
and "Backend" have different meanings.

## Decision Drivers 

* Improve separation of concerns
* Allow parallel development of two distinct and independent domains
* Have two separate CI steps for each part
* Unblock the potential creation of a server-side JSON-based API
* Reduce deployment time for the backend repository

## Considered Options

* Keep the "monorepo"
* Tweak build system to run only necessary CI steps
* Split code repository into separate client/server side code repositories

## Effort for splitting the repository
* Fork the repo
* Separate either domains, removing language from opposite domain
* Adapt deployment script to pull from either repos and go through build steps for both
* Separate documentation, testing
* Separate out git commits that are relevant to the domain, without losing the history of the repo
* Clean out JS side, but leave old commits in PHP code
* Create CI for JavaScript build
* Tweak development environment to allow standalone and integrated
  development on either or both code bases.

## Decision Outcome

Since the tweaking of the build system would introduce addionional
complexities in the build and CI system and would not solve the naming
confusion, we decided to split the repository in two, named
`fundraising-app` for the server-side code and `fundraising-app-frontend`
for the client-side code. We'll also take the opportunity to move the code
form GitHub to GitLab.

We track the renaming of the `fundraising-backend` repository to
`fundraising-operation-center` 
in a [separate ticket](https://phabricator.wikimedia.org/T246796)


