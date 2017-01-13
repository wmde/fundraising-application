#!/bin/sh -x

sudo add-apt-repository ppa:ondrej/php -y

apt-get update
apt-get upgrade

# Avoid MySQL password prompt
#debconf-set-selections <<< 'mysql-server mysql-server/root_password password PASSWORD_HERE'
#debcnf-set-selections <<< 'mysql-server mysql-server/root_password_again password PASSWORD_HERE'

apt-get install -y mysql-client unzip build-essential # mysql-server
apt-get install -y php7.1 php7.1-dev php7.1-intl php7.1-sqlite3 php7.1-curl php7.1-xml php7.1-mysql
