FROM php:7.1-fpm

RUN mkdir -p /usr/share/nginx/www/spenden.wikimedia.de/current/var/log
RUN chown www-data:www-data /usr/share/nginx/www/spenden.wikimedia.de/current/var/log

RUN apt-get update \
    # for intl
    && apt-get install -y libicu-dev \
    # for curl
    && apt-get install -y libcurl3-dev \
    # for xml
    && apt-get install -y libxml2-dev \
    # for konto_check
    && apt-get install -y unzip patch libz-dev \
    # compare vagrant/install_packages.sh
    #&& docker-php-ext-install -j$(nproc) pdo_sqlite \
    && docker-php-ext-install -j$(nproc) intl curl xml pdo_mysql mbstring

# compare vagrant/installKontoCheck.sh
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
