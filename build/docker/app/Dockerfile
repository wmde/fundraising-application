FROM php:7.1-fpm

RUN apt-get update \
    && docker-php-ext-install -j$(nproc) intl curl \
    && wget http://sourceforge.net/projects/kontocheck/files/konto_check-de/5.8/konto_check-5.8.zip/download \
    && unzip download \
    && cd konto_check-5.* \
        && mkdir -p xcache \
        && tar -xf xcache.tar.gz -C xcache --strip-components=1 \
        && rm xcache.tar.gz \
        && ( \
            cd xcache \
            && phpize \
            && ./configure \
            && make -j$(nproc) \
            && make install \
        ) \
        && cp /tmp/konto_check-5.8/php/konto_check.ini /etc/php/7.1/mods-available \
        && docker-php-ext-enable konto_check

kontocheck extension


cd /tmp
rm -rf /tmp/konto_check-5.8

cp blz.lut2f /vagrant/res
unzip php.zip
cd php
patch -p0 < /vagrant/build/kontocheck58-php7.patch
phpize
./configure
make
make install
cp /tmp/konto_check-5.8/php/konto_check.ini /etc/php/7.1/mods-available
phpenmod
