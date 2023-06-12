# Creating and Editing Email Templates

## Twig
The tool used to render the emails is called [Twig](https://twig.symfony.com/). It is a php based renderer which makes it a little easier to work with templates. 

## Templates
The email templates are located in `app/mail_templates`. These templates do not contain any copy. Instead, they contain references to text snippets that are also twig files, located in the [Fundraising Content Repository](https://github.com/wmde/fundraising-frontend-content). The templates in `app/mail_templates` may also contain conditional logic to display different snippets, depending on in input data (e.g. payment type, membership type, moderation, etc).

## Testing
There is an email integration test located in `tests/integration/MailTemplatesTest.php` which tests the output of the templates. It does not use the real copy for the tests, and instead checks the import paths against rendered files that are located in `tests/data/GeneratedMailTemplates`.

### MailTemplateFixtures
We don't test the templates directly, instead we generate different types of emails our system based off the emails it sends. If you look in `tests/data/GeneratedMailTemplates` you can see the same templates are rendered multiple times, but contain different combinations of content.

These are specified in `app/MailTemplateFixtures/MailTemplateFixtures.php`.

### Generating the Test Data
To generate the files we test against you delete the templates in `tests/data/GeneratedMailTemplates` and run the `MailTemplatesTest`. Before running tests it will generate the test files.

Because the generated templates have been just created, these tests will then pass. That means when you re-generate the templates it is important to then acceptance test them. You can do this by manually diffing them from the old ones to make sure they're correct.

## Adding a new template
1. Add your template file. If you're using conditional logic, pay close attention to the generated whitespace and use [whitespace modifiers](https://twig.symfony.com/doc/3.x/templates.html#whitespace-control) on opening and closing Twig tags to avoid unnecessary blank lines in the output.
2. Add settings for testing it into the `getTemplateProviders` function of `MailTemplateFixtures`.
   1. If your template is doesn't use any conditional logic, you can use a `SimpleSettingsGenerator`.
   2. If your template has conditional logic you should create a `VariantSettingsGenerator` with variants for each condition.
3. Run the `MailTemplatesTest` and it will generate new template files for you to use and test them.
