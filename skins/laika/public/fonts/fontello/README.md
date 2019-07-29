# Fontello Icon Font
The icon font files (fontello.*) were created using http://fontello.com/.
In order to keep the font files as small as possible, only icons we actually need are included in the font file and a snapshot of all icons we use is stored in `config.json` in this directory.

## Updating the font file
Open up http://fontello.com/ and drag and drop the `config.json` file from this directory into the web page. This will select all previously used icons on the page.
You can now edit the icon selection and either add or remove icons from the font file. Once you are done, download the icon font set.
The downloaded package will include an updated `config.json` which needs to replace the current `config.json` which ensures that the configuration file always represents our current icon selection stored in Git.

## Updating icon CSS

After updating all fontello font files, also make sure to also update the CSS classes for the individual icons in `font.scss`.
Each icon must have a variable with the respective character code declared.
These character codes can then later be used as part of the `content` CSS property of pseudo elements:

```
$icon-warning: '\f0f3'; /* Warning Bell */

[...]

.mdi-alert:before {
	content: $icon-warning;
}
```

Fontello auto-generates these identifiers and provides them in the `fontello.css` file which is part of the font download, you can grab these character codes directly from the font package you have downloaded.
