# Fundraising Application - Single- or Multi-Page-Application Architecture?

Date: 2021-06-22

Deciders: Abban Dunne, Corinna Hillebrand, Gabriel Birke

Technical Stories: https://phabricator.wikimedia.org/T241751

## Status

Accepted

## Context and Problem Statement

The original Fundraising Application was a classic web-based Multi-Page-Application with some JavaScript for progressive enhancement and state stored on the Server. The 2016 rewrite introduced some state on the client. The new skin we introduced in 2019 renders the template variables in an HTML data attribute, but the client-side markup is rendered with Vue. It is a "hybrid" Multi-Page-Application, where the client-side code still depends on the server side variables, but the server-side "templating" is obsolete. 

The current architecture has the following drawbacks:

* Switching pages does a full page reload
* Our naive client-side "subpage" mechanism breaks the browser history (see https://phabricator.wikimedia.org/T285046 )
* Components shared across pages need to be compiled into the entry point
	for each page.
* We need "Full Stack" Developers who are familiar with backend
	technologies (PHP, Symfony, databases, PHP CI tools, Docker) and frontend technologies
	(TypeScript, Vue, SASS, bundler and CI tools).

## Decision Drivers

* User experience - fast page load times, browser history, immediate feedback of
	what's happening, keep focus on current task.
* Developer experience (ease-of-use, fewest dependencies possible) 

## Considered Options

* Single-Page-Application (SPA) + API
* MPA with Server-Side-Rendering
* MPA with progressive enhancement
* HTML-only MPA

## Decision Outcome

A separation into an API and Single-Page-Application looks like the best
option, regarding our decision drivers:

* It's improving on an already good user experience.
* It won't add complexity for developers beyond the status quo
* It's "open" enough to improve our technology stack, we don't tie
	ourselves to a specific library.

A Server-Rendered Multi-Page-Application with progressive enhancement
looks like a promising architecture for the far future. We will keep
observing the available technologies.

## Pros and Cons of the Options

### Single-Page-Application (SPA) with API

In this scenario, the client-side code does its own routing and history,
communicating with the server side via API (instead of HTTP form POST
requests).

* Pro: Easy to have front ends in different technologies (donating from banner, trying out other technologies)
* Pro: "Going with the flow" of current frontend technologies / assumed best practices
* Pro: It's a popular choice -> lots of documentation, community, development and (long-term-)support
* Pro: Better decoupling of frontend and backend - easier testing and possibility to split team efforts
* Pro: Potential knowledge sharing with other WMDE teams
* Pro: Smaller code chunks and faster loading times
* Pro: Gradual transition is possible (islands of MPA in a SPA or vice versa)
* Pro: Can do shiny loading and navigation transitions
* Con: Navigation and routing needs additional client-side code (Vue router)
* Con: Duplicated domain on the client-side
* Con: Need to maintain (and keep in sync) with API
* Con: Decoupling creates unnecessary overhead for the small team
* Con: Effort for adding/duplicating client-side A/B test bucketing logic and storage
* Con: Initial page load shows blank page (could be improved with Server-Side-Rendering of Vue components)


### MPA with Server-Side-Rendering

This solution could have two different implementations:

1. Render templates on the server, then use the library 
   [Stimulus.js](https://symfony.com/blog/new-in-symfony-the-ux-initiative-a-new-javascript-ecosystem-for-symfony)
   to render small markup update snippets on the server when the user 
   interacts with the page.
2. Use [Inertia.js](https://inertiajs.com/) to use existing server-side
   routing while still using Vue on the frontend. This makes our hybrid
   MPA behave like a SPA.

* Pro (Stimulus): Less complex frontend stack (1 backend library instead)
* Pro (Inertia): Application is a MPA that behaves like a SPA.
* Con: Client-server communication for every user interaction (latency) -> bad with German mobile internet
* Con: Technology in its infancy
* Con: We'd still inevitably need some JavaScript library for form fields
* Con: Hard to search for help (stimulus and inertia are common English words)
* Con: New technology to learn (bad for overall velocity and onboarding)
* Con (Inertia): Unclear how to integrate Webpack chunks
* 

### MPA with progressive enhancement

The PHP backend would render the templates again, with some special
attributes that libraries for progressive enhancement would hook into.
Contenders are [Alpine.js](https://alpinejs.dev/) and [HTMX](https://htmx.org/).

* Pro: Small stack, easy to pick up
* Con: We can't foresee when that approach breaks. Our requirements could 
  already be too complex for what the libraries can do. We would need to 
  create a prototype to verify that the technology is suitable.
* Con: Unfamiliar library (harder onboarding, some learning curve)
* Con: We still need a JavaScript library for complex form elements
	(dropdowns, select boxes) and need to integrate it with the
	progressive enhancement solution.
* Con: The technology is still in its infancy - we don't know if it 
   will still be supported in several years.

### HTML-Only MPA

* Pro: Reduced stack size
* Pro: Compatible with all devices
* Con: Sub-par user experience:
	* Frequent page reloads and re-renders
	* The user loses context and has to wait
	* Form fields look "ugly" and outdated, not adapted to page design.

