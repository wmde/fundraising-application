js:
	npm run build-assets ; npm run copy-assets

clear:
	rm -rf var/cache/

ui: clear js

.PHONY: js clear ui