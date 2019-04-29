# Use naming styling CSS schema

Date: 2019-04-29

## Status

Accepted

## Context

We are rewriting our client-side from scratch. That gives us the opportunity to restructure and rethink our css classes.
We discussed two CSS schemas `naming classes as components` vs `naming classes according to styling`.

Naming classes as components - This will make it more clear where the css classes are used, but will duplicate what we already reflect in our markup

Naming classes as styling - This will make the css classes reusable

## Decision

We will name CSS classes according to the style they deliver instead of the component they are used in. 

## Consequences

We have to make sure we don't diverge from the schema because it feels more convinient for some cases.  
We will match the naming style of the components framework we use.
