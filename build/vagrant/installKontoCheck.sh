#!/usr/bin/env bash

cd /tmp
rm -rf /tmp/konto_check-5.8
wget http://sourceforge.net/projects/kontocheck/files/konto_check-de/5.8/konto_check-5.8.zip/download
unzip download
cd konto_check-5.*
cp blz.lut2f /vagrant/res
unzip php.zip
cd php
patch -p0 < /vagrant/build/kontocheck58-php7.patch
phpize
./configure
make
make install
cp /tmp/konto_check-5.8/php/konto_check.ini /etc/php/7.0/mods-available
phpenmod konto_check

