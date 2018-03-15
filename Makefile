current_user = $(shell id -u)
current_group = $(shell id -g)

js-install:
	-mkdir -p tmp/home
	-echo "node:x:$(current_user):$(current_group)::/var/nodehome:/bin/bash" > tmp/passwd
	docker run --rm -it --user $(current_user):$(current_group) -v $(PWD):/data:delegated -w /data -v $(PWD)/tmp/home:/var/nodehome:delegated -v $(PWD)/tmp/passwd:/etc/passwd node:8 npm install --prefer-offline

js:
	docker run --rm -it --user $(current_user):$(current_group) -v $(PWD):/data:delegated -w /data -e NO_UPDATE_NOTIFIER=1 node:8 npm run build-assets
	docker run --rm -it --user $(current_user):$(current_group) -v $(PWD):/data:delegated -w /data -e NO_UPDATE_NOTIFIER=1 node:8 npm run copy-assets

clear:
	rm -rf var/cache/

ui: clear js

.PHONY: js js-install clear ui