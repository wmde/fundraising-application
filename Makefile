current_user  := $(shell id -u)
current_group := $(shell id -g)

COMPOSER_FLAGS :=
DOCKER_FLAGS  := --interactive --tty
TEST_DIR      :=
BUILD_DIR     := $(PWD)
MIGRATION_VERSION :=
MIGRATION_CONTEXT :=
APP_ENV       := dev
ASSET_BRANCH  := main

DOCKER_IMAGE  := registry.gitlab.com/fun-tech/fundraising-frontend-docker

.DEFAULT_GOAL := ci

up-app: down-app
	docker-compose -f docker-compose.yml up -d

up-debug: down-app
	docker-compose -f docker-compose.yml -f docker-compose.debug.yml up -d

down-app:
	docker-compose -f docker-compose.yml -f docker-compose.debug.yml down > /dev/null 2>&1

# Installation

setup: create-env download-assets install-php default-config setup-db

create-env:
	if [ ! -f .env ]; then echo "APP_ENV=dev">.env; fi

install-php:
	docker run --rm $(DOCKER_FLAGS) --volume $(BUILD_DIR):/app -w /app --volume /tmp:/tmp --volume ~/.composer:/composer --user $(current_user):$(current_group) $(DOCKER_IMAGE):composer composer install $(COMPOSER_FLAGS)

update-php:
	docker run --rm $(DOCKER_FLAGS) --volume $(BUILD_DIR):/app -w /app --volume ~/.composer:/composer --user $(current_user):$(current_group) $(DOCKER_IMAGE):composer composer update $(COMPOSER_FLAGS)

setup-db:
	docker-compose run --rm start_dependencies
	docker-compose run --rm app ./vendor/bin/doctrine orm:generate-proxies var/doctrine_proxies

drop-db:
	docker-compose run --rm app ./vendor/bin/doctrine orm:schema-tool:drop --force

default-config:
	cp -i build/app/config.dev.json app/config

download-assets:
	build/download_assets.sh $(ASSET_BRANCH)

# Maintenance

clear:
	rm -rf var/cache/
	docker-compose run --rm --no-deps app rm -rf var/cache/

# n alias to avoid frequent typo
clean: clear

# Continuous Integration

ci: phpunit cs validate-app-config validate-campaign-config stan lint-container

ci-with-coverage: phpunit-with-coverage cs validate-app-config validate-campaign-config stan lint-container

phpunit-system:
	docker-compose run --rm --no-deps app ./vendor/bin/phpunit tests/System/

lint-container:
	docker-compose run --rm --no-deps app ./bin/console lint:container

validate-app-config:
	docker-compose run --rm --no-deps app ./bin/console app:validate:config app/config/config.dist.json app/config/config.test.json

validate-campaign-config:
	docker-compose run --rm --no-deps app ./bin/console app:validate:campaigns $(APP_ENV)

validate-campaign-utilization:
	docker-compose run --rm --no-deps app ./bin/console app:validate:campaigns:utilization

# Code Quality

test: phpunit

phpunit:
	docker-compose run --rm --no-deps app php -d memory_limit=1G vendor/bin/phpunit $(TEST_DIR)

phpunit-with-coverage:
	docker-compose -f docker-compose.yml -f docker-compose.debug.yml run --rm --no-deps -e XDEBUG_MODE=coverage app_debug php -d memory_limit=1G vendor/bin/phpunit --configuration=phpunit.xml.dist --stop-on-error --coverage-clover coverage.clover

cs:
	docker-compose run --rm --no-deps app ./vendor/bin/phpcs

fix-cs:
	docker-compose run --rm --no-deps app ./vendor/bin/phpcbf

stan:
	docker run --rm -it --volume $(BUILD_DIR):/app -w /app $(DOCKER_IMAGE):stan analyse --level=1 --no-progress cli/ src/ tests/


phpmd:
	docker-compose run --rm --no-deps app ./vendor/bin/phpmd src/ text phpmd.xml

.PHONY: up-app down-app up-debug setup create-env download-assets install-php update-php setup-db drop-db default-config clear clean ui test ci ci-with-coverage phpunit phpunit-with-coverage phpunit-system cs fix-cs stan validate-app-config validate-campaign-config validate-campaign-utilization lint-container phpmd
