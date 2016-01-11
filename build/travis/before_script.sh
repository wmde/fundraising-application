set -ex

original_dir=$(pwd)
	cd /tmp
	wget http://sourceforge.net/projects/kontocheck/files/latest/download
	unzip download
	cd konto_check-5.*
	cp blz.lut2f ${original_dir}/res
	unzip php.zip
	cd php
	phpize
	./configure
	make
	sudo make install
	phpenv config-add konto_check.ini
cd ${original_dir}
