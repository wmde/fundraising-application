#!/bin/bash -x

# Add PHP repo
add-apt-repository ppa:ondrej/php -y

# Add node.js repo
curl -sL https://deb.nodesource.com/setup_6.x | bash -

apt-get update
#apt-get upgrade -y

# Avoid MySQL password prompt
export DEBIAN_FRONTEND="noninteractive"
debconf-set-selections <<< 'mysql-server mysql-server/root_password password PASSWORD_HERE'
debconf-set-selections <<< 'mysql-server mysql-server/root_password_again password PASSWORD_HERE'

apt-get install -y unzip build-essential
apt-get install -y mysql-client mysql-server
apt-get install -y php7.1 php7.1-dev php7.1-intl php7.1-sqlite3 php7.1-curl php7.1-xml php7.1-mysql php7.1-mbstring
apt-get install -y nodejs
