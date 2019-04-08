current_user  := $(shell id -u)
current_group := $(shell id -g)
BUILD_DIR     := $(PWD)
TMPDIR        := $(BUILD_DIR)/tmp
COMPOSER_FLAGS :=
NPM_FLAGS     := --prefer-offline
DOCKER_FLAGS  := --interactive --tty
TEST_DIR      :=
REDUX_LOG     :=
UNIQUE_APP_CONTAINER := $(shell uuidgen)-app
MIGRATION_VERSION :=
APP_ENV       := dev

.DEFAULT_GOAL := ci

up_app: down_app
	docker-compose -f docker-compose.yml up -d

up_debug: down_app
	docker-compose -f docker-compose.yml -f docker-compose.debug.yml up -d

down_app:
	docker-compose -f docker-compose.yml -f docker-compose.debug.yml down > /dev/null 2>&1

build_app_composer:
	@mkdir -p $(TMPDIR)
	@docker build -t wmde/fundraising-frontend-composer --target app_composer build/app > $(TMPDIR)/composer_build.log 2>&1

install-js:
	-mkdir -p $(TMPDIR)/home
	-echo "node:x:$(current_user):$(current_group)::/var/nodehome:/bin/bash" > $(TMPDIR)/passwd
	docker run --rm $(DOCKER_FLAGS) --user $(current_user):$(current_group) -v $(BUILD_DIR):/data:delegated -w /data -v $(TMPDIR)/home:/var/nodehome:delegated -v $(TMPDIR)/passwd:/etc/passwd node:10 npm install $(NPM_FLAGS)

install-php: build_app_composer
	docker run --rm $(DOCKER_FLAGS) --volume $(BUILD_DIR):/app -w /app --volume /tmp:/tmp --volume ~/.composer:/composer --user $(current_user):$(current_group) wmde/fundraising-frontend-composer composer install $(COMPOSER_FLAGS)

update-js:
	-mkdir -p $(TMPDIR)/home
	-echo "node:x:$(current_user):$(current_group)::/var/nodehome:/bin/bash" > $(TMPDIR)/passwd
	docker run --rm $(DOCKER_FLAGS) --user $(current_user):$(current_group) -v $(BUILD_DIR)/skins/cat17:/data:delegated -w /data -v $(TMPDIR)/home:/var/nodehome:delegated -v $(TMPDIR)/passwd:/etc/passwd node:10 npm update $(NPM_FLAGS)
	docker run --rm $(DOCKER_FLAGS) --user $(current_user):$(current_group) -v $(BUILD_DIR)/skins/10h16:/data:delegated -w /data -v $(TMPDIR)/home:/var/nodehome:delegated -v $(TMPDIR)/passwd:/etc/passwd node:10 npm update $(NPM_FLAGS)
	docker run --rm $(DOCKER_FLAGS) --user $(current_user):$(current_group) -v $(BUILD_DIR)/skins/wmde19:/data:delegated -w /data -v $(TMPDIR)/home:/var/nodehome:delegated -v $(TMPDIR)/passwd:/etc/passwd node:10 npm update $(NPM_FLAGS)

audit-js:
	-mkdir -p $(TMPDIR)/home
	-echo "node:x:$(current_user):$(current_group)::/var/nodehome:/bin/bash" > $(TMPDIR)/passwd
	docker run --rm $(DOCKER_FLAGS) --user $(current_user):$(current_group) -v $(BUILD_DIR)/skins/cat17:/data:delegated -w /data -v $(TMPDIR)/home:/var/nodehome:delegated -v $(TMPDIR)/passwd:/etc/passwd node:10 npm audit fix $(NPM_FLAGS)
	docker run --rm $(DOCKER_FLAGS) --user $(current_user):$(current_group) -v $(BUILD_DIR)/skins/10h16:/data:delegated -w /data -v $(TMPDIR)/home:/var/nodehome:delegated -v $(TMPDIR)/passwd:/etc/passwd node:10 npm audit fix $(NPM_FLAGS)
	docker run --rm $(DOCKER_FLAGS) --user $(current_user):$(current_group) -v $(BUILD_DIR)/skins/wmde19:/data:delegated -w /data -v $(TMPDIR)/home:/var/nodehome:delegated -v $(TMPDIR)/passwd:/etc/passwd node:10 npm audit fix $(NPM_FLAGS)

update-php: build_app_composer
	docker run --rm $(DOCKER_FLAGS) --volume $(BUILD_DIR):/app -w /app --volume ~/.composer:/composer --user $(current_user):$(current_group) wmde/fundraising-frontend-composer composer update $(COMPOSER_FLAGS)

default-config:
	cp build/app/config.dev.json app/config

js:
	docker run --rm $(DOCKER_FLAGS) --user $(current_user):$(current_group) -v $(BUILD_DIR):/data:delegated -w /data -e NO_UPDATE_NOTIFIER=1 -e REDUX_LOG=$(REDUX_LOG) node:10 npm run build-assets
	docker run --rm $(DOCKER_FLAGS) --user $(current_user):$(current_group) -v $(BUILD_DIR):/data:delegated -w /data -e NO_UPDATE_NOTIFIER=1 node:10 npm run copy-assets

watch-js:
	docker run --rm $(DOCKER_FLAGS) --user $(current_user):$(current_group) -v $(BUILD_DIR):/data:delegated -w /data/skins/cat17 -e NO_UPDATE_NOTIFIER=1 -e REDUX_LOG=$(REDUX_LOG) node:10 npm run watch

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

phpunit-with-coverage:
	docker-compose -f docker-compose.yml -f docker-compose.debug.yml run --rm --name $(UNIQUE_APP_CONTAINER)-$@ app_debug ./vendor/bin/phpunit --configuration=phpunit.xml.dist --coverage-clover coverage.clover --printer="PHPUnit\TextUI\ResultPrinter"

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
	docker-compose run --rm --no-deps --name $(UNIQUE_APP_CONTAINER)-$@ app ./console app:validate:campaigns $(APP_ENV)

validate-campaign-utilization:
	docker-compose run --rm --no-deps --name $(UNIQUE_APP_CONTAINER)-$@ app ./console app:validate:campaigns:utilization

phpmd:
	docker-compose run --rm --no-deps --name $(UNIQUE_APP_CONTAINER)-$@ app ./vendor/bin/phpmd src/ text phpmd.xml

npm-ci:
	docker run --rm $(DOCKER_FLAGS) --user $(current_user):$(current_group) -v $(BUILD_DIR):/code -w /code -e NO_UPDATE_NOTIFIER=1 node:10 npm run ci

migration-execute:
	docker-compose run --rm --no-deps app vendor/doctrine/migrations/bin/doctrine-migrations migrations:execute $(MIGRATION_VERSION) --up --configuration=vendor/wmde/fundraising-store/migrations.yml

migration-revert:
	docker-compose run --rm --no-deps app vendor/doctrine/migrations/bin/doctrine-migrations migrations:execute $(MIGRATION_VERSION) --down --configuration=vendor/wmde/fundraising-store/migrations.yml

migration-status:
	docker-compose run --rm --no-deps app vendor/doctrine/migrations/bin/doctrine-migrations migrations:status --configuration=vendor/wmde/fundraising-store/migrations.yml

ci: covers phpunit cs npm-ci validate-app-config validate-campaign-config stan

ci-with-coverage: covers phpunit-with-coverage cs npm-ci validate-app-config validate-campaign-config stan

setup: install-php install-js default-config ui setup-db

.PHONY: ci ci-with-coverage clean clear covers cs install-php install-js js npm-ci npm-install phpmd phpunit phpunit-system setup stan test ui validate-app-config validate-campaign-config
