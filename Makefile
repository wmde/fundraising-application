current_user  := $(shell id -u)
current_group := $(shell id -g)
BUILD_DIR     := $(PWD)
TMPDIR        := $(BUILD_DIR)/tmp
COMPOSER_FLAGS :=
NPM_FLAGS     := --prefer-offline
DOCKER_FLAGS  := --interactive --tty

install-js:
	-mkdir -p $(TMPDIR)/home
	-echo "node:x:$(current_user):$(current_group)::/var/nodehome:/bin/bash" > $(TMPDIR)/passwd
	docker run --rm $(DOCKER_FLAGS) --user $(current_user):$(current_group) -v $(BUILD_DIR):/data:delegated -w /data -v $(TMPDIR)/home:/var/nodehome:delegated -v $(TMPDIR)/passwd:/etc/passwd node:8 npm install $(NPM_FLAGS)

install-php:
	docker run --rm $(DOCKER_FLAGS) --volume $(BUILD_DIR):/app -w /app --volume ~/.composer:/composer --user $(current_user):$(current_group) composer install --ignore-platform-reqs $(COMPOSER_FLAGS)

js:
	docker run --rm $(DOCKER_FLAGS) --user $(current_user):$(current_group) -v $(BUILD_DIR):/data:delegated -w /data -e NO_UPDATE_NOTIFIER=1 node:8 npm run build-assets
	docker run --rm $(DOCKER_FLAGS) --user $(current_user):$(current_group) -v $(BUILD_DIR):/data:delegated -w /data -e NO_UPDATE_NOTIFIER=1 node:8 npm run copy-assets

clear:
	rm -rf var/cache/

ui: clear js

test: covers phpunit

setup:
	docker-compose run --rm app ./vendor/bin/doctrine orm:schema-tool:create

covers:
	docker-compose run --rm --no-deps app ./vendor/bin/covers-validator

phpunit:
	docker-compose run --rm app ./vendor/bin/phpunit

phpunit-system:
	docker-compose run --rm app ./vendor/bin/phpunit tests/System/

cs:
	docker-compose run --rm --no-deps app ./vendor/bin/phpcs

fix-cs:
	docker-compose run --rm --no-deps app ./vendor/bin/phpcbf

stan:
	docker-compose run --rm --no-deps app php -d memory_limit=-1 vendor/bin/phpstan analyse --level=1 --no-progress cli/ contexts/ src/ tests/

validate-app-config:
	docker-compose run --rm --no-deps app ./console validate-config app/config/config.dist.json app/config/config.test.json

phpmd:
	docker-compose run --rm --no-deps app ./vendor/bin/phpmd src/ text phpmd.xml

npm-ci:
	docker run --rm $(DOCKER_FLAGS) --user $(current_user):$(current_group) -v $(BUILD_DIR):/code -w /code -e NO_UPDATE_NOTIFIER=1 node:8 npm run ci

ci: covers phpunit cs npm-ci validate-app-config stan

.PHONY: ci clear covers cs install-php install-js js npm-ci npm-install phpmd phpunit phpunit-system setup stan test ui validate-app-config
