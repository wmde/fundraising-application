set -ex

original_dir=$(pwd)
	cd /tmp
	wget http://sourceforge.net/projects/kontocheck/files/konto_check-de/5.8/konto_check-5.8.zip/download
	unzip download
	cd konto_check-5.*
	cp blz.lut2f ${original_dir}/res
	unzip php.zip
	cd php
	patch -p0 < ${original_dir}/build/kontocheck58-php7.patch
	phpize
	./configure
	make
	sudo make install
	phpenv config-add konto_check.ini
cd ${original_dir}
