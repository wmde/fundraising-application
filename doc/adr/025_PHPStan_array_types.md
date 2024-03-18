# How to type arrays with PHPStan annotations

Date: 2024-03-15

Deciders: Abban Dunne, Corinna Hillebrand, Gabriel Birke, Tanuja D.

## Status

Accepted

## Context

Arrays are a pervasive data structure in PHP. PHP does not differentiate
between list, tuple and hash-map data types, you use an array for all of
these. And for many libraries and parts of our own code, we're also using
hash-maps with defined keys instead of objects. All the while PHP lacks
one feature: strict typing for array keys and values.

PHPStan allows to make up for PHP's deficiencies for doing a static
analysis on the code. PHPStan allows (and, on higher check levels even requires)
to create phpdoc annotations that specify [array shapes][1].

PHPStan also allows to give those array shapes names, using the
`@phpstan-type` annotation. This is a feature similar to the [Type
Aliases][2] in TypeScript. Custom types have the following benefits:
- they help structure complex, nested array shapes into more manageable chunks
- they make the code easier to read because the reader does not have to
    parse different bracket types and maybe even nested shapes with
    different brackets (e.g `array{EmailAddress,array<string,mixed>}[]`
    which represents a list of tuples containing an email address and a
    hash map).
- custom types give the array a meaningful name

The arguments against using custom types are as follows:
1. They are tool-specific and not native to the language. If we switch
   tools or the language gets new features, we have to change the
   annotations. While current IDEs support the syntax, the support is not
   as good as the support for native language features.
2. When reviewing code, it's unclear where they come from, because they
   can be defined in PHP packages (violating the "One Class per File" rule
   of PHP autoloading conventions) and the PHPStan configuration.
3. They can be confused with class names. Without a rigorous naming 
   convention, a type definition in the annotation can look like a
   class, contradicting the native PHP type that is `array`
4. It creates a layer of indirection - you'd have to train for not only
   looking at `use` statements to see where a names comes from, but also
   `@phpstan-import-type` statements and/or the PHPStan configuration
   file.

## Decision

We won't use custom type definitions for arrays in our code.

We will treat nested or hard-to read structures as "code smells", trying
to find better, object-oriented structures. Where possible, we will use
classes (value objects) instead of arrays.

## Consequences

There are four "types" of arrays - Lists, Maps, Tuples, and "object
shapes". From now own, we'll try to to apply the following guidelines
concerning the usage and conversion of these types:

### Lists

If the code iterates the array without "random access" (like it would do
with a map), use the list-style annotation, using the type of the contents
of the array. Array functions and `for` or `foreach` loops are good
indicators that the code uses the array as a list.

Examples: `EmailAddress[]`, `Donation[]`, `string[]`

### Maps

The best indication for a map type is a string key or random access to
specific integer keys and exactly *one* value type.

Sometimes the "exactly one value type" criterion can be deceiving - if you
see the code using a set of specific string values then the array in
question is most likely a "pseudo-object", an object shape (see below).

Examples:
- `@param array<int,string> $statusCodeToMessage`
- `@return array<string,RouteFunction>`
- `return array<string,bool> Moderation reason flags being active or not`

### Tuples

A fixed-length array with specific types, mostly used as a return type for
functions that should return more than one result item. On their own,
tuples are OK and helpful, especially with the destructuring syntax. If
you have a list of tuples or nest them in other classes, that should be a
sign to refactor the tuple into a value object.

Example Code for "good" tuple usage:

```php
function sendMail() {
    [ $recipient, $body ] = $this->prepareEmail();
    // some code
}

/**
 * @return array{EmailAddress,EmailBody}
 */
function prepareEmail() {
    // get the values from somewhere;
    return [ $recipient, $body ]
}

```

Example annotations for "smelly" tuple usage:

- `@var array{EmailAddress,EmailBody}[] $preparedEmails`
- `@var array{array{EmailAddress,EmailBody},EmailAddress,EmailHeaders} fullEmail`

### Object-Like arrays / complex shapes

These data structures come from earlier PHP versions that did not allow
for the easy definition of value objects (PHP was missing named parameters,
readonly properties and typed properties). We agree to turn these arrays
into value objects (ideally with readonly constructor properties) when we
change code that uses those arrays. This will help us give a proper name
to the concept that's "hiding" inside the array definition.

Example:

```
array{firstName:string,lastName:String,age:int}
// becomes

class Person {
    public function __construct(
        public readonly string $firstName,
        public readonly string $lastName
        public readonly int $
    ){}
}

```

[1]: https://phpstan.org/writing-php-code/phpdoc-types#array-shapes
[2]: https://www.typescriptlang.org/docs/handbook/2/everyday-types.html#type-aliases

