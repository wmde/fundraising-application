current_user  := $(shell id -u)
current_group := $(shell id -g)
BUILD_DIR     := $(PWD)
TMPDIR        := $(BUILD_DIR)/tmp
COMPOSER_FLAGS :=
NPM_FLAGS     := --prefer-offline
DOCKER_FLAGS  := --interactive --tty
TEST_DIR :=
REDUX_LOG :=
UNIQUE_APP_CONTAINER := $(shell uuidgen)-app
MIGRATION_VERSION :=

.DEFAULT_GOAL := ci

install-js:
	-mkdir -p $(TMPDIR)/home
	-echo "node:x:$(current_user):$(current_group)::/var/nodehome:/bin/bash" > $(TMPDIR)/passwd
	docker run --rm $(DOCKER_FLAGS) --user $(current_user):$(current_group) -v $(BUILD_DIR):/data:delegated -w /data -v $(TMPDIR)/home:/var/nodehome:delegated -v $(TMPDIR)/passwd:/etc/passwd node:8 npm install $(NPM_FLAGS)

install-php:
	docker run --rm $(DOCKER_FLAGS) --volume $(BUILD_DIR):/app -w /app --volume ~/.composer:/composer --user $(current_user):$(current_group) composer install --ignore-platform-reqs $(COMPOSER_FLAGS)

update-js:
	-mkdir -p $(TMPDIR)/home
	-echo "node:x:$(current_user):$(current_group)::/var/nodehome:/bin/bash" > $(TMPDIR)/passwd
	docker run --rm $(DOCKER_FLAGS) --user $(current_user):$(current_group) -v $(BUILD_DIR)/skins/cat17:/data:delegated -w /data -v $(TMPDIR)/home:/var/nodehome:delegated -v $(TMPDIR)/passwd:/etc/passwd node:8 npm update $(NPM_FLAGS)
	docker run --rm $(DOCKER_FLAGS) --user $(current_user):$(current_group) -v $(BUILD_DIR)/skins/10h16:/data:delegated -w /data -v $(TMPDIR)/home:/var/nodehome:delegated -v $(TMPDIR)/passwd:/etc/passwd node:8 npm update $(NPM_FLAGS)

update-php:
	docker run --rm $(DOCKER_FLAGS) --volume $(BUILD_DIR):/app -w /app --volume ~/.composer:/composer --user $(current_user):$(current_group) composer update --ignore-platform-reqs $(COMPOSER_FLAGS)

default-config:
	cp build/app/config.prod.json app/config

js:
	docker run --rm $(DOCKER_FLAGS) --user $(current_user):$(current_group) -v $(BUILD_DIR):/data:delegated -w /data -e NO_UPDATE_NOTIFIER=1 -e REDUX_LOG=$(REDUX_LOG) node:8 npm run build-assets
	docker run --rm $(DOCKER_FLAGS) --user $(current_user):$(current_group) -v $(BUILD_DIR):/data:delegated -w /data -e NO_UPDATE_NOTIFIER=1 node:8 npm run copy-assets

clear:
	rm -rf var/cache/
	docker-compose run --rm --no-deps --name $(UNIQUE_APP_CONTAINER)-$@ app rm -rf var/cache/

# n alias to avoid frequent typo
clean: clear

ui: clear js

test: covers phpunit

setup-db:
	docker-compose run --rm start_dependencies
	docker-compose run --rm app ./vendor/bin/doctrine orm:schema-tool:create
	docker-compose run --rm app ./vendor/bin/doctrine orm:generate-proxies var/doctrine_proxies

covers:
	docker-compose run --rm --no-deps --name $(UNIQUE_APP_CONTAINER)-$@ app ./vendor/bin/covers-validator

phpunit:
	docker-compose run --rm --name $(UNIQUE_APP_CONTAINER)-$@ app ./vendor/bin/phpunit $(TEST_DIR)

phpunit-system:
	docker-compose run --rm --name $(UNIQUE_APP_CONTAINER)-$@ app ./vendor/bin/phpunit tests/System/

cs:
	docker-compose run --rm --no-deps --name $(UNIQUE_APP_CONTAINER)-$@ app ./vendor/bin/phpcs

fix-cs:
	docker-compose run --rm --no-deps --name $(UNIQUE_APP_CONTAINER)-$@ app ./vendor/bin/phpcbf

stan:
	docker-compose run --rm --no-deps --name $(UNIQUE_APP_CONTAINER)-$@ app php -d memory_limit=-1 vendor/bin/phpstan analyse --level=1 --no-progress cli/ src/ tests/

validate-app-config:
	docker-compose run --rm --no-deps --name $(UNIQUE_APP_CONTAINER)-$@ app ./console app:validate:config app/config/config.dist.json app/config/config.test.json

validate-campaign-config:
	docker-compose run --rm --no-deps --name $(UNIQUE_APP_CONTAINER)-$@ app ./console app:validate:campaigns

phpmd:
	docker-compose run --rm --no-deps --name $(UNIQUE_APP_CONTAINER)-$@ app ./vendor/bin/phpmd src/ text phpmd.xml

npm-ci:
	docker run --rm $(DOCKER_FLAGS) --user $(current_user):$(current_group) -v $(BUILD_DIR):/code -w /code -e NO_UPDATE_NOTIFIER=1 node:8 npm run ci

migration-execute:
	docker-compose run --rm --no-deps app vendor/doctrine/migrations/bin/doctrine-migrations migrations:execute $(MIGRATION_VERSION) --up --configuration=vendor/wmde/fundraising-store/migrations.yml

migration-revert:
	docker-compose run --rm --no-deps app vendor/doctrine/migrations/bin/doctrine-migrations migrations:execute $(MIGRATION_VERSION) --down --configuration=vendor/wmde/fundraising-store/migrations.yml

migration-status:
	docker-compose run --rm --no-deps app vendor/doctrine/migrations/bin/doctrine-migrations migrations:status --configuration=vendor/wmde/fundraising-store/migrations.yml

ci: covers phpunit cs npm-ci validate-app-config validate-campaign-config stan

setup: install-php install-js default-config ui setup-db

.PHONY: ci clean clear covers cs install-php install-js js npm-ci npm-install phpmd phpunit phpunit-system setup stan test ui validate-app-config validate-campaign-config
