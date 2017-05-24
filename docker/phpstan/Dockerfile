# phpstan in docker
# used as sanity check of code when installed via "composer install --no-dev" (so on prod) w/o other dev tools to check

FROM phpstan/phpstan:latest

RUN apk add --no-cache --virtual .persistent-deps icu-dev libxml2-dev \
    && docker-php-ext-configure intl --enable-intl \
    && docker-php-ext-install intl
