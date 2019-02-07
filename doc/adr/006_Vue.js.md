# Using Vue.js as the new frontend framework

Date: 2019-02-07

## Status

Accepted

## Context

Most of the JavaScript Code on the FundraisingFrontend already has the [[ https://facebook.github.io/flux/docs/in-depth-overview.html#content | Flux architecture ]] with its one-way data flow (using [[ https://redux.js.org/ | Redux ]]), but it does not use one of the popular reactive "component" frameworks like Vue or React. Instead, it uses self-written "components" (two-way binding with handling of DOM events) and "view handlers" (one-way binding DOM manipulators) that are tied to the markup of the page via jQuery, in an attempt to do progressive enhancement and decouple the markup from the functionality. 

The current JavaScript code has several drawbacks:

* **JavaScript resource size.** The components and stores are built as one big "library" file (called `wmde.js`, with a global object called `WMDE`), instead of having separate "entry points" for the different pages (donation, membership). The current "entry points", `donationForm.js` and `membershipForm`, add one more JavaScript resource that the browsers needs to download, adding HTTP overhead and latency.
* **Hard to understand.** While the [[ https://github.com/wmde/FundraisingFrontend/blob/master/doc/HOWTO_Create_a_form.md | architecture is documented ]], the setup lacks the in-depth explanations, code snippets and tutorials that common frameworks come with. Also, the code itself does not use modern ECMAScript features (classes, spread operator, arrow functions). Instead it uses  custom, factory-function based "classes", making the code harder to understand.
* **Hard to extend.** Where Vue and React have ecosystems attached to them, all code for the current JavaScript - asynchronous validation, connecting the store to the views, etc - is custom, adding to the accidental complexity and maintenance burden.
* **Hard to reuse.** While the self-written components are quite flexible and "pluggable", the entry point scripts are very long and hard to understand, since there is no hierarchy of elements. Instead, they are a big factory that initializes all the classes. That initialization code is duplicated across scripts.


## Decision

Going forward, we will use Vue to render the frontend. We chose it for the following reasons:

* It's a mature, tested, well-documented widely used framework with an open source license and an active ecosystem
* There is already some knowledge about Vue in the developer and UX teams
* Vue is used in wikidata, making knowledge sharing easier. 

We will write all new features in Vue and ECMAScript 2015 (and later), refactoring and cleaning up the existing code base. The redux "reducers" and "state aggregators" will move to Vuex modules, while the Twig templates, "view handlers" and "components" of the old code base become Vue components. 


## Consequences

* We are able to develop new features and A/B tests faster
* We can re-use individual components and page elements easier 
* The onboarding experience of new developers improves
* We are taking one step towards standardizing our stack
 