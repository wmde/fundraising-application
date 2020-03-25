# The architecture & conventions of the "Laika" Skin

## Pages
From its historic roots as a PHP application, the Fundraising Application output is structured into "pages". In older skins, the application rendered the pages as [Twig](http://twig.symfony.com) templates, each page with a template file. In Laika, all the templates are the same, extending the `Base_Layout` template which renders an application "shell": a headers section and a `<div id="#app">` element that  Vue uses as its mount point.

Each page has its own Vue "application", consisting of 4 parts:

* A root Vue component in `laika/src/components/pages`
* An "entry point" script (written in TypeScript) in `laika/src/pages`. It contains the bootstrapping code for the application: Getting the initialization data (see below), rendering the root component
* A template in `laika/templates`, referencing the compiled entry point script
* An entry in `vue.config.js`. Vue-CLI will send this entry point to Webpack, which will compile the code into JavaScript.

## Data initialization

The Twig templating engine renders all data assigned to it in the `data-application-vars` attribute of the "mount point div", as JSON. Each entry point script defines an interface that describes the expected data and uses the `PageDataInitializer` class to read it and pass on the data as properties to the root component. At the moment, pageDataInitializer does not check if the data has the right type and is complete.

`PageDataInitializer` extracts the `selectedBuckets` variable from the application variables and puts it into a separate property. Use this variable as an input for the [a/b testing features (CSS, Vue, Store) in the client-side code](HOWTO_Create_an_a_b_test.md).

`PageDataInitializer` also reads two other data attributes from the div:

* `messages` - contains all i18n messages from the `fundraising-frontend-content` repository
* `assetsPath` - path to assets (JS, CSS, images, fonts) of the laika skin

## Component hierarchy, reuse and using the store
When thinking about modularization of components, apply the following rules of thumb:

* If a root component becomes too large, you should break it up into smaller components. Put them in a subdirectory in `laika/src/components/pages`, named after the root component (in snake_case). 
* Keep the communication with the store (through the `mapState` and `mapGetters` helper functions and the `$store.dispatch` method) in the root component.
* If a page (root component) has "subpages" that don't correspond to a PHP route, put them in a subdirectory following the naming schema `laika/src/pages/root_component_name/subpages`. Move the communication with the store to the subpages, the root component should handle the display logic for the subpages.
* If you reuse components across pages, put them in the `laika/src/components/shared` directory. Shared components are not allowed to communicate with the store! If you catch yourself adding `if` statements for different use cases to shared components, move them back to the root component subdirectory and live with the duplication. Adding features that are not used by all parent components is ok, though.
* Only add new state to the store if that state needs to be shared across child components.
* Always dispatch actions to the store, never use mutations from the components! This ensures a consistent asynchronous interface. If you need to wrap mutations in actions for that, do it.


## Possible for improvements for the Laika architecture
* Investigate build speed - [T241851](https://phabricator.wikimedia.org/T241851)
* Build CSS only once, not for every entry point.
* Increase Type safety:
  * remove `any` and casts.
  * Better type validation of server-side data in  `PageDataInitializer`, maybe using https://github.com/joanllenas/ts.data.json or https://github.com/gcanti/io-ts
  * Use Vue 3.0 composition api
  * Rethink state management: Either make Vuex more type safe by using [vuex-class](https://github.com/ktsn/vuex-class) or [vuex-module-decorators](https://github.com/championswimmer/vuex-module-decorators) or gradually replace Vuex with a different reactive state managemnt solution that is a better fit for the composition API. Maybe [use the composition API for state management](https://medium.com/everything-full-stack/vue-composition-api-as-a-state-management-cbc509f5a717).
* Rebuild frontend whenever messages change & modularize messages, instead of including all messages on every request. We can tackle this, when we've [containerized the infrastructure](https://phabricator.wikimedia.org/T225597).
* Remove Twig, use more simple variable collection mechanism instead - [T248460](https://phabricator.wikimedia.org/T248460)

