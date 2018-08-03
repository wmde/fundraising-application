# URL Schema

Date: 2018-07-31

## Status

Accepted

## Context

When we developed the Fundraising application, we did not pay close attention to the URL schema and ended up with three different styles:

* "Slashy", pseudo-[REST][1]-style URLs like `/donation/add`
* "action-sentences" like `/apply-for-membership`
* a combination of both like `/contact/get-in-touch`

We don't do search engine optimization (SEO) in the form of "meaningful, localized and stable URLs", as the main traffic to the donation page comes from banners and we don't have much relevant content to that search engines can index.

## Decision

We will use the "action-sentence" style for URLs in the future. They should follow the pattern `verb-noun` or `verb-preposition-noun`.

Our reasoning behind the decision:

* They convey more information about what the route does, because we can use all verbs of the English language instead of restricting us to `GET` and `POST`.
* REST-style URLs are deceiving because our application has no real API and is not explicitly written with a [RESTful][1] architecture.
* We can still have a dedicated REST API in the future, by using the `/api` route.
* The sentence style fits better to our use case architecture, which also read more like sentences.

Whenever we change a URL, we decide if we need to create a redirect from the old one to the new in the NGinX configuration. GET support is a good indicator for the need for a redirect. If route is more like a functional "endpoint" like `donation/update`, then we don't need a redirect.

If we need to add i18n information to the URL at some point, we will do it with a "subdirectory prefix", e.g. `/de/apply-for-membership`, `/en/apply-for-membership`. The cons listed at https://support.google.com/webmasters/answer/182192?hl=en do not outweigh the benefits.

## Consequences

Whenever we touch a route which does not follow the "action-sentence" style, we change it and decide on adding a redirect to the old one.

[1]: https://en.wikipedia.org/wiki/Representational_state_transfer
