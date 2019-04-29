# Use the framework Buefy

Date: 2019-04-26

## Status

Accepted

## Context

We don't want to use the old css/bootstrap code for the new skin. Therefor we looked at a few different frameworks which can help reduce our efforts in writing css classes and vue components from scratch.

### Vuetify

It is a big collection of ready-made UI elements based on Google's Material Design. It looks like it could bloat our JavaScript and CSS and make the page look very generic. Also, binding to Material Design might be tricky due to overwriting lots of Material Design assumptions.

### Buefy

A "lightweight" component library. Looks nice and does a lot of work for us. It is based on Bulma CSS which seems to be established and is a good combination between usable and adaptable.

### Element.io

The documentation does not look good enough. The Q&A page on their website is not in English.

### Tailwind CSS

It is not a component library, but a collection of useful CSS classes for rapid prototyping components (by adding lots and lots of class names to them). When the prototyping phase is over, we need to combine the CSS classes into components. We are not sure we want to go into the direction of "CSS Framework external to components".

## Decision

We will use Buefy, a UI components framework for Vue.js based on Bulma which is a free, open source CSS framework based on Flexbox.

## Consequences

We will be able to use already built components.  
We will have responsive design out of the box.  
It will take some time for us to "learn" to use this particular framework as noone in the team has any previous experience with it.