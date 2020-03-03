# Fundraising frontend content repository

[![Build Status](https://travis-ci.org/wmde/fundraising-frontend-content.svg?branch=master)](https://travis-ci.org/wmde/fundraising-frontend-content)

This is a repository for the textual content of the fundraising application.

## Content Organization
* `data`: Language-independent content that is used internally. Currently it's the black- and whitelists for address fields and comments.
* `i18n`: Language-dependent content.
	* `de_DE`: Sub folders named by language/locale identifier. Each language has a dedicated subdirectory.
		* `data`: Language-dependent configuration options, like the list of allowed page URLs.
		* `mail`: Mail template snippets. For mail templates that consist of multiple snippets, the templates are placed in a subdirectory. The files must not contain HTML!
		* `messages`: JSON files with translations.
		* `shared`: Content that is used both for E-Mails and in the web application.
		* `web`: Content that is used in the web application. This content may contain simple, valid HTML.
			* `pages`: Content for pages defined in `data/pages.json`
* `resources`: Web resources that will be available through the web server, like images or PDFs.
	* `...`: Language-independent resources (e.g. logos).
	* `de_DE`: Sub folders named by language/locale identifier. Each language has a dedicated subdirectory.
		* `...`: Language-dependent resources (e.g. documents containing text).

## FAQ

### Where to put translations?
If they are very short snippets of text like labels or messages, place them in `messages/messages.json`. Otherwise create a file in `mail`, `shared` or `web`.

### What HTML tags are allowed?
Have a look at the [list of allowed tags in the content provider Repository](https://github.com/wmde/fundraising-content-provider/blob/master/src/HtmlPurifier.php#L21-L28).

### What template syntax is allowed?
Only `{% include %}` and `{$ variable $}` statements.

