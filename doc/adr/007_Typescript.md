# Using Typescript

Date: 2019-02-07

## Status

Accepted

## Context

During the modernization of the client-side code outlined in "[ADR 006 - Using Vue.js](006_Vue.js.md)" and the first prototype for that project - the Address Change Form - we also tried out using [TypeScript](https://www.typescriptlang.org/). After the project we evaluated if we want to continue to use TypeScript for client side code. We collected following arguments. 

### Learning Curve
Nobody of us has experience with TypeScript, there will be a learning curve and possible slowdowns while setting up features.

### Migration Pain
Migrating the existing JavaScript code to TypeScript looks daunting. However, we're already planning a rewrite of the the Frontend with Vue and Vuex and want to spare us the pain of another "rewrite" with Typescript. Instead, all code will be written in TypeScript.   

### How "Future-Proof" is TypeScript?
With the decline of CoffeeScript in our minds, we don't know if TypeScript is "just a fad" and we'll have to port the client-side code to another language in 3-5 years. We're optimistic that TypeScript will still be in active development in the Future, since it's both open source and has Microsoft as a corporate "sponsor" behind it. 

### How well-established is TypeScript?
Compared to JavaScript there are less tutorials and reference articles. However, since TypeScript is "JavaScript with types", we can apply all the existing material. We are confident that the TypeScript-specific documentation will improve. We are hopeful that the `tslint` linter will get feature-parity with `eslint`. 

### Onboarding
Having to learn TypeScript might slow down the onboarding process of new developers. 

### "Sunk Cost"
We now have working code that uses TypeScript. Removing it would incur additional effort. In terms of effort spent, we encountered biggest time sink already - the initial setup of the test and build environment. We can build on that from now on. 

### Maintainability and Readability
Typescript will make our code more maintainable, because it helps us to avoid low-level type errors during code changes. The TypeScript interfaces document our intentions and our domain language. Our experiences with PHP, moving from code without type hints to code with type hints support that argument. This is also the main argument and driver of the final decision.   

### Tooling support
Typed code helps IDEs to show errors while writing the code and allows for easy refactoring and code navigation.

### Vue.js 3.0
Vue.js 3.0 is rewritten in TypeScript. We assume there will be benefits from using TypeScript as well and that their decision is based on criteria that we are not even aware of yet (see "Learning Curve").

## Decision

* We will use TypeScript as our client-side language, because the benefit of maintainability is the most important argument for us.
* We will document the personal "best practices" in our team, documenting the structure of our client side code and how we write TypeScript.

## Consequences

Our velocity when developing client-side code will increase, because the code is more maintainable.

We will switch our test framework from [tape](https://github.com/substack/tape) to [Jest](https://jestjs.io/), since the [tape author won't officially support Typescript](https://github.com/substack/tape/issues/353) and there is more documentation and support around Jest.

There might be initial slowdowns when developing new features or when onboarding developers without TypeScript experience.
