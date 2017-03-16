#!/bin/bash -ex

if [[ -z "$DB_PASSWD" ]]; then
    DB_PASSWD="INSECURE PASSWORD"
fi

mysql -pPASSWORD_HERE -u root -e 'CREATE DATABASE IF NOT EXISTS fundraising;'
mysql -pPASSWORD_HERE -u root -e "CREATE USER IF NOT EXISTS 'fundraising'@'localhost' IDENTIFIED BY '$DB_PASSWD';"
mysql -pPASSWORD_HERE -u root -e "GRANT ALL ON fundraising.* TO 'fundraising'@'localhost';"

sed -i -e "s/__DB_PASSWORD__/$DB_PASSWD/" /vagrant/app/config/config.prod.json

cd /vagrant
vendor/bin/doctrine orm:schema-tool:create
vendor/bin/doctrine orm:generate-proxies var/doctrine_proxies
cd -