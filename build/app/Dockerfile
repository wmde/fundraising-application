FROM php:7.2-fpm as app

RUN mkdir -p /usr/share/nginx/www/spenden.wikimedia.de/current/var/cache \
    && mkdir -p /usr/share/nginx/www/spenden.wikimedia.de/current/var/doctrine_proxies \
    && mkdir -p /usr/share/nginx/www/spenden.wikimedia.de/current/var/log \
    && chown -R www-data:www-data /usr/share/nginx/www/spenden.wikimedia.de/current/var

RUN apt-get update \
    # for intl
    && apt-get install -y libicu-dev \
    # for curl
    && apt-get install -y libcurl3-dev \
    # for xml
    && apt-get install -y libxml2-dev \
    # for konto_check
    && apt-get install -y unzip libz-dev \
    #&& docker-php-ext-install -j$(nproc) pdo_sqlite \
    && docker-php-ext-install -j$(nproc) intl curl xml pdo_mysql mbstring

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

RUN apt-get install -y ssmtp \
    && echo "mailhub=mailhog:1025" >> /etc/ssmtp/ssmtp.conf

COPY ./mailhog.ini /usr/local/etc/php/conf.d/mailhog.ini

# xDebug-enabled debug container configuration below

FROM app as app_debug

RUN pecl install xdebug-2.6.0 \
    && docker-php-ext-enable xdebug \
    && echo "xdebug.remote_enable=on" >> /usr/local/etc/php/conf.d/xdebug.ini \
    && echo "xdebug.remote_autostart=off" >> /usr/local/etc/php/conf.d/xdebug.ini

FROM app AS app_composer

RUN apt-get install -y git subversion mercurial bash patch make zip libzip-dev \
    && docker-php-ext-configure zip --with-libzip \
    && docker-php-ext-install -j$(nproc) zip

COPY --from=composer:1.8 /usr/bin/composer /usr/bin/composer

ENV COMPOSER_HOME /tmp

WORKDIR /app

CMD ["composer"]
