# phpstan in docker
# used as sanity check of code when installed via "composer install --no-dev" (so on prod) w/o other dev tools to check

FROM docker.io/composer:1.6

RUN \
	# for intl
	apk add --no-cache --virtual .persistent-deps icu-dev && \
	# for curl
	apk add --no-cache --virtual .persistent-deps curl-dev && \
	# for xml
	apk add --no-cache --virtual .persistent-deps libxml2-dev && \
	docker-php-ext-configure intl --enable-intl && \
	docker-php-ext-install intl curl xml pdo_mysql mbstring

ENV KONTOCHECK_VERSION 6.08

RUN \
	docker-php-source extract && \
	cd /tmp && \
	curl -Ls -o konto_check-$KONTOCHECK_VERSION.zip https://sourceforge.net/projects/kontocheck/files/konto_check-de/$KONTOCHECK_VERSION/konto_check-$KONTOCHECK_VERSION.zip/download && \
	unzip konto_check-*.zip && \
	cd konto_check-$KONTOCHECK_VERSION && \
	cp blz.lut2f /etc/blz.lut && \
	unzip php.zip && \
	cd php && \
	docker-php-ext-configure /tmp/konto_check-$KONTOCHECK_VERSION/php && \
	docker-php-ext-install /tmp/konto_check-$KONTOCHECK_VERSION/php && \
	docker-php-source delete && \
	rm -rf /tmp/konto_check-*

RUN composer global require phpstan/phpstan ^0.11
ENTRYPOINT [ "/tmp/vendor/bin/phpstan" ]
