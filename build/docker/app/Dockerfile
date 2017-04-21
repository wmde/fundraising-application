FROM php:7.1-fpm

COPY kontocheck58-php7.patch /tmp/kontocheck58-php7.patch

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
    && docker-php-ext-install -j$(nproc) intl curl xml pdo_mysql mbstring \
    # compare vagrant/installKontoCheck.sh
    && cd /tmp \
    && curl -OLs http://sourceforge.net/projects/kontocheck/files/konto_check-de/5.8/konto_check-5.8.zip/download \
    && unzip download \
    && cd konto_check-5.* \
    && mkdir -p /vagrant/res \
    && cp blz.lut2f /vagrant/res \
    && unzip php.zip \
    && ( \
        cd php \
        && patch -p0 < /tmp/kontocheck58-php7.patch \
        && phpize \
        && ./configure \
        && make -j$(nproc) \
        && make install \
    ) \
    && cp /tmp/konto_check-5.8/php/konto_check.ini /usr/local/etc/php \
    && docker-php-ext-enable konto_check
