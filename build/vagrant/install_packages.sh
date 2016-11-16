#!/bin/sh -x

apt-get update
apt-get upgrade

# Avoid MySQL password prompt
#debconf-set-selections <<< 'mysql-server mysql-server/root_password password PASSWORD_HERE'
#debcnf-set-selections <<< 'mysql-server mysql-server/root_password_again password PASSWORD_HERE'

apt-get install -y php7.0 php7.0-dev php7.0-intl php7.0-sqlite php7.0-curl php7.0-mysql mysql-server mysql-client unzip build-essential