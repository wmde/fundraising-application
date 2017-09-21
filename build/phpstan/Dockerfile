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

ENV KONTOCHECK_VERSION 5.8

RUN \
	docker-php-source extract && \
	cd /tmp && \
	curl -Ls -o konto_check-$KONTOCHECK_VERSION.zip https://sourceforge.net/projects/kontocheck/files/konto_check-de/$KONTOCHECK_VERSION/konto_check-$KONTOCHECK_VERSION.zip/download && \
	curl -Ls -o php7.zip https://sourceforge.net/projects/kontocheck/files/konto_check-de/$KONTOCHECK_VERSION/php7.zip/download && \
	unzip konto_check-*.zip && \
	unzip php7.zip && \
	cd konto_check-5.* && \
	cp blz.lut2f /etc/blz.lut && \
	unzip php.zip && \
	cd php && \
	cp /tmp/php/konto_check.c . && \
	# see https://sourceforge.net/p/kontocheck/bugs/17/
	sed -i -e 's/Z_TYPE_PP/Z_TYPE_P/g' konto_check.c && \
	sed -i -e 's/Z_LVAL_PP/Z_LVAL_P/g' konto_check.c && \
	docker-php-ext-configure /tmp/konto_check-$KONTOCHECK_VERSION/php && \
	docker-php-ext-install /tmp/konto_check-$KONTOCHECK_VERSION/php && \
	docker-php-source delete

RUN composer global require phpstan/phpstan ^0.9
ENTRYPOINT [ "/tmp/vendor/bin/phpstan" ]
