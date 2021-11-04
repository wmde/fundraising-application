# Salutations

Date: 2021-10-01

Deciders: Abban Dunne, Corinna Hillebrand, Gabriel Birke

Technical Story: https://phabricator.wikimedia.org/T220365

## Status

Accepted


## Context

The fundraising department wants to address donors and members in emails
and other communication with a personalized greeting, tailored to the
preferred way people want to be adressed. 

Our external data warehousing provider only allows for a limited selection
of three German genders: "Herr", "Frau" and "Divers".

We want to keep the business logic (bounded contexts) free from gender
norms and I18N concerns. We also want to keep the UI extensible (for
future options in the selection field) and translatable.


## Decision

We put salutation information into the content repository that contains
the following information for each of the three possible salutations:

- The "label", used in the selection field in forms and for display on
	confirmation pages.
- The "value", used in the domain (will probably the same as label)
- Translation keys for addressing in varying degrees of formality

Our frontend code will use the label and value to construct selection fields.
Our server code will use the value and the translation keys for creating
emails and writing to the database. The export script will map the
different values from the database to the allowed 3 values for the data
warehousing provider.

## Consequences

With this design, we don't need to change any code to add more salutation
options. The only change will happen in the I18N files.

When our data warehousing provider becomes more lenient, we can add an
input field for preferred salutation and relax the strict mapping rules in
the export.

## Links

* [Falsehoods programmers believe about gender](https://gist.github.com/garbados/f82604ea639e0e47bf44)

