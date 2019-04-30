# Use naming styling CSS schema

Date: 2019-04-29

## Status

Accepted

## Context

We are rewriting our client-side from scratch. That gives us the opportunity to restructure and rethink our css classes.
We discussed two CSS schemas `naming classes as components` vs `naming classes according to visual properties`.

Naming classes as components - This will make it more clear where the css classes are used, but will duplicate what we already reflect in our markup. A variation of this would be to use the [Block-Element-Modifier (BEM)](http://getbem.com/introduction/) pattern. 

Naming classes as styling - This will make the CSS classes reusable and fits well with the naming schema of our CSS framework, [Bulma](https://bulma.io/). Examples of this schema are the [Atomic CSS](https://github.com/nemophrost/atomic-css) schema and [Tailwind CSS](https://tailwindcss.com/docs/what-is-tailwind/). There were several small concerns with this style:

* The style makes our markup less performant: because each element has several classes, the resulting size will be bigger. Since the markup will be compressed anyway and the compression algorithm is efficient for reducing repeating patterns, this is not an issue.
* We might end up with "lying" class names, e.g. `.is-red { color: blue}`. This will be mitigated by the style names having a slightly higher and more purpose-oriented schema, e.g. `.primary-color`. We strive to achieve a sweet spot between the extremes of BEM and Atomic CSS.
* The added classes make the HTML source less readable. "Readability", especially when compared to BEM, is kind of subjective.

## Decision

We will name CSS classes according to the styling they produce instead of the component they are used in. Adding component class names to Vue components feels like a violation of the [DRY principle](https://en.wikipedia.org/wiki/Don%27t_repeat_yourself).

We will share as much of those styling classes between components as possible, instead of each component defining their own styles.

## Consequences

* We have to make sure we don't diverge from the schema because it feels more convenient for some cases.  
* We will match the naming style of the components framework we use.

