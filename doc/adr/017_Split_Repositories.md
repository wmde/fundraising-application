# Splitting repositories 

Date: 2021-03-01

Deciders: Gabriel Birke, Corinna Hillebrandt, Abban Dunne, Conny Kawohl

Technical Story: https://phabricator.wikimedia.org/T273800

## Status

Proposed

## Context and Problem Statement

We propose the splitting of the Fundraising Frontend App into two distinct repositories, Fundraising Frontend App and Fundraising Backend App. This would allow for a clearer separation of concerns, helping distinguish between architectures and languages (currently predominantly JavaScript on the frontend and PHP on the backend side). It would furthermore ease naming schemes for either domain, and allow for domain-specific tickets. Longer deployment processes for the frontend due to its tooling would no longer bog down backend-only tasks.

## Decision Drivers 

* Reduction of deployment times for the backend repository
* Improved separation of concerns
* Parallel development of two distinct and independent domains, option to separate CI steps
* Unblocks the potential creation of a server-side JSON-based API

## Considered Options

* Tweak build system by separating CI processes

## Effort
* Fork the repo
* Separate either domains, removing language from opposite domain
* Adapt deployment script to pull from either repos and go through build steps for both
* Separate documentation, testing
* Separate out git commits that are relevant to the domain, without losing the history of the repo
* Clean out JS side, but leave old commits in PHP code
* Create CI for JavaScript build

## Decision Outcome

**To be discussed**

Chosen option: "[option 1]", because [justification. e.g., only option, which meets k.o. criterion decision driver | which resolves force force | … | comes out best (see below)].

### Positive Consequences <!-- optional -->

**To be discussed**

* [e.g., improvement of quality attribute satisfaction, follow-up decisions required, …]
* …

### Negative Consequences <!-- optional -->

**To be discussed**

* [e.g., compromising quality attribute, follow-up decisions required, …]
* …

