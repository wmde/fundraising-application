# Common practices for client-side code

These guidelines are agreed-upon practices by the FUN team and reflect their current understanding of Vue.js and TypeScript. 

* We collect all new types in the `types.ts` file. When it becomes sufficiently large, we'll have enough code "examples" to refactor them into smaller cohesive units/modules.
* When writing a Vuex store module, don't split mutations, actions, getters, etc in separate files. Doing this makes the code harder to navigate. If a Vuex module file becomes too big, ask the team members if splitting is ok.
* In Vue.js files, use TypeScript
* Don't use [vue-class-component decorator](https://github.com/vuejs/vue-class-component)
