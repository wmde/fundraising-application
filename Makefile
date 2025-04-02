current_user  := $(shell id -u)
current_group := $(shell id -g)

COMPOSER_FLAGS :=
DOCKER_FLAGS  := --interactive --tty
COVERAGE_FLAGS := --coverage-html coverage
TEST_DIR      :=
BUILD_DIR     := $(PWD)
MIGRATION_VERSION :=
MIGRATION_CONTEXT :=
APP_ENV       := dev
ASSET_BRANCH  := main
DOCTRINE_SCHEMA_COMMAND := docker compose run --rm app ./bin/doctrine orm:schema-tool:create --dump-sql | sed 's/The following SQL statements will be executed://; s/CREATE TABLE/CREATE TABLE IF NOT EXISTS/'

DOCKER_IMAGE  := registry.gitlab.com/fun-tech/fundraising-frontend-docker

.DEFAULT_GOAL := ci

# Local Development Environment

up-app: down-app validate-dev-config
	docker compose up -d

down-app:
	docker compose down > /dev/null 2>&1

validate-dev-config:
ifeq ($(wildcard app/config/config.dev.json),)
	$(error File 'app/config/config.dev.json' does not exist. Please run 'make default-config')
endif
ifeq ($(wildcard app/config/paypal_api.dev.yml),)
	$(error File 'app/config/paypal_api.dev.yml' does not exist. Please run 'make default-config')
endif
	docker compose run --rm --no-deps app ./bin/console app:validate:config app/config/config.dist.json app/config/config.dev.json

# This needs the "drone" and "pass" cli tools to be installed on or local machine
drone-ci:
	@echo -n "GITHUB_TOKEN=" > var/.drone-secrets.txt
	@pass wmde-fun/github-composer-ci-token >> var/.drone-secrets.txt
	-@drone exec --secret-file var/.drone-secrets.txt --trusted .drone.yml ; \
	RET=$$?; \
	rm -f var/.drone-secrets.txt; \
	if [ $$RET -eq 0 ]; then echo "\n\n\033[0;32mDrone CI passed\033[0m\n\n"; else echo "\n\n\033[0;31mDrone CI failed\033[0m\n\n"; fi; \
	exit $$RET

# Installation

setup: create-env download-assets install-php default-config setup-doctrine clear

create-env:
	if [ ! -f .env ]; then echo "APP_ENV=dev">.env; fi

default-config:
	-cp -i .docker/app/config.dev.json app/config || true
	-cp -i tests/Data/files/paypal_api.yml app/config/paypal_api.dev.yml || true

install-php:
	docker run\
		--rm $(DOCKER_FLAGS) \
		--volume $(BUILD_DIR):/app \
		-w /app \
		--volume /tmp:/tmp \
		--volume ~/.composer:/composer \
		--user $(current_user):$(current_group) \
		$(DOCKER_IMAGE):latest \
		composer install $(COMPOSER_FLAGS)

update-php:
	docker run\
		--rm $(DOCKER_FLAGS) \
		--volume $(BUILD_DIR):/app \
		-w /app \
		--volume ~/.composer:/composer \
		--user $(current_user):$(current_group) \
		$(DOCKER_IMAGE):latest \
		composer update $(COMPOSER_FLAGS)

update-content:
	docker run \
		--rm $(DOCKER_FLAGS) \
		--volume $(BUILD_DIR):/app \
		-w /app \
		--volume ~/.composer:/composer \
		--user $(current_user):$(current_group) \
		$(DOCKER_IMAGE):latest \
		composer update wmde/fundraising-frontend-content


generate-database-schema:
	$(DOCTRINE_SCHEMA_COMMAND) > ./.docker/database/01.Database_Schema.sql

validate-sql:
	$(DOCTRINE_SCHEMA_COMMAND) | diff - ./.docker/database/01.Database_Schema.sql 1>&2

setup-doctrine:
	docker compose run --rm start_dependencies
	docker compose run --rm app ./bin/doctrine orm:generate-proxies var/doctrine_proxies

drop-db:
	docker compose run --rm app ./bin/doctrine orm:schema-tool:drop --force

download-assets:
	./bin/download_assets.sh $(ASSET_BRANCH)

# Maintenance

clear:
	docker compose run --rm --no-deps app sh -c "if [ -d /tmp/symfony/cache ]; then rm -rf /tmp/symfony/cache/*; chown -R www-data:www-data /tmp/symfony; fi"

# an alias to avoid frequent typo
clean: clear

# Continuous Integration

ci: phpunit cs validate-app-config validate-campaign-config stan lint-container

ci-with-coverage: phpunit-with-coverage cs validate-app-config validate-campaign-config stan lint-container

phpunit-system:
	docker compose run --rm --no-deps app ./vendor/bin/phpunit tests/System/

lint-container:
	docker compose run --rm --no-deps app ./bin/console lint:container

validate-app-config:
	docker compose run --rm --no-deps app ./bin/console app:validate:config app/config/config.dist.json app/config/config.test.json
	docker compose run --rm --no-deps app ./bin/console app:validate:config app/config/config.dist.json .docker/app/config.dev.json

validate-campaign-config:
	docker compose run --rm --no-deps app ./bin/console app:validate:campaigns $(APP_ENV)

validate-campaign-utilization:
	docker compose run --rm --no-deps app ./bin/console app:validate:campaigns:utilization

# Code Quality

test: phpunit

phpunit:
	docker compose run --rm --no-deps app php -d memory_limit=1G vendor/bin/phpunit $(TEST_DIR)

phpunit-with-coverage:
	docker compose \
		run --rm --no-deps app \
		php -d memory_limit=1G -d xdebug.mode=coverage \
		vendor/bin/phpunit --configuration=phpunit.xml.dist --stop-on-error $(COVERAGE_FLAGS)

cs:
	docker compose run --rm --no-deps app ./vendor/bin/phpcs

fix-cs:
	-docker compose run --rm --no-deps app ./vendor/bin/phpcbf

stan:
	docker run \
		--rm -it \
		--volume $(BUILD_DIR):/app \
		-w /app \
		$(DOCKER_IMAGE):latest \
		php -d memory_limit=1G \
		vendor/bin/phpstan analyse --level=9 --no-progress cli/ src/ tests/

phpmd:
	docker compose run --rm --no-deps app ./vendor/bin/phpmd src/ text phpmd.xml

.PHONY: up-app down-app setup create-env download-assets install-php update-php generate-database-schema validate-sql setup-doctrine drop-db default-config clear clean ui test ci ci-with-coverage phpunit phpunit-with-coverage phpunit-system cs fix-cs stan validate-app-config validate-campaign-config validate-campaign-utilization lint-container phpmd
