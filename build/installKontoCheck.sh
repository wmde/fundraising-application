#!/bin/bash

set -ex

original_dir=$(pwd)

cd /tmp
rm -rf /tmp/konto_check-5.8
wget -q -O konto_check-5.8.zip http://sourceforge.net/projects/kontocheck/files/konto_check-de/5.8/konto_check-5.8.zip/download
wget -q -O php7.zip https://sourceforge.net/projects/kontocheck/files/konto_check-de/5.8/php7.zip/download
unzip konto_check-5.8.zip
unzip php7.zip
cd konto_check-5.8
cp blz.lut2f /usr/local/etc/
unzip php.zip
cd php
cp /tmp/php/konto_check.c .
# see https://sourceforge.net/p/kontocheck/bugs/17/
sed -i -e 's/Z_TYPE_PP/Z_TYPE_P/g' konto_check.c
sed -i -e 's/Z_LVAL_PP/Z_LVAL_P/g' konto_check.c
phpize
./configure
make
make install
if [ -x "$(command -v phpenv)" ]; then
	# environments with php version management (e.g. travis)
	phpenv config-add konto_check.ini
else
	# environments with one php installation
	cp konto_check.ini /etc/php/7.1/mods-available
	phpenmod konto_check
fi

cd ${original_dir}
