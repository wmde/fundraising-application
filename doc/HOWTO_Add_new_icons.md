# How to add new icons to skins

- Go to [icomoon.io](https://icomoon.io/app/#/select "Icomoon's App")
- Click `Import Icons` and upload `assets/fonts/wikimedia/icomoonwikimedia.svg`
- Add/change icons per requirement
- Click `Generate Font`
- Click the icon next to the Download button (settings)
	- Include `variables.scss` in download
	- Keep the "icon-" prefix for generated (s)css
	- Make sure the iconset is named `icomoonwikimedia`
- Download the zip file
- In FundraisingFrontend:
	- Extract `variables.scss` & `style.scss` to `components/fonts/icomoonwikimedia`
	- Extract the contents of the fonts folder to `assets/fonts/wikimedia/`
	- Ensure that selectors in `components/fonts/icomoonwikimedia/delta` still match your icon names / are still needed