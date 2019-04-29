# Use the CSS framework Buefy

Date: 2019-04-26

## Status

Accepted

## Context

The old skin uses the Bootstrap 3, which uses the `float` and `clear` CSS properties for layout, instead of the more modern `flexbox`. Therefore, we looked at different frameworks which can help reduce our efforts in writing CSS classes and Vue components from scratch.

### [Vuetify](https://vuetifyjs.com/en/)

It is a big collection of ready-made UI elements based on Google's Material Design. It looks like it could bloat our JavaScript and CSS and make the page look very generic. Also, binding to Material Design might be tricky due to overwriting lots of Material Design assumptions.

### [Buefy](https://buefy.org/)

A "lightweight" component library. Looks nice and does a lot of work for us. It is based on [Bulma CSS](https://bulma.io/) which seems to be established and is a good combination between usable and adaptable.

### [Element.io](https://element.eleme.io/#/en-US)

The documentation does not look good enough. The Q&A page on their website is not in English.

### [Tailwind CSS](https://tailwindcss.com/docs/what-is-tailwind/)

It is not a component library, but a collection of useful CSS classes for rapid prototyping components (by adding lots and lots of class names to them). When the prototyping phase is over, we need to combine the CSS classes into components. We are not sure we want to go into the direction of "CSS Framework external to components".

## Decision

We will use Buefy, a UI components framework for Vue.js based on Bulma which is a free, open source CSS framework based on Flexbox.

## Consequences

* We will be able to use already built components.  
* We will have responsive design out of the box.  
* It will take some time for us to "learn" to use this particular framework as no one in the team has any previous experience with it.