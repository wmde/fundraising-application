js:
	rm -rf var/cache/ ; npm run build-assets ; npm run copy-assets

.PHONY: js