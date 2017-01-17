#!/bin/bash -ex

if [[ -z "$DB_PASSWD" ]]; then
    DB_PASSWD="INSECURE PASSWORD"
fi

mysql -pPASSWORD_HERE -u root -e 'CREATE DATABASE fundraising;'
mysql -pPASSWORD_HERE -u root -e "CREATE USER 'fundraising'@'localhost' IDENTIFIED BY '$DB_PASSWD';"
mysql -pPASSWORD_HERE -u root -e "GRANT ALL ON fundraising.* TO 'fundraising'@'localhost';"

# TODO move config file creation to application_config provisioner
cp /vagrant/build/vagrant/config.prod.json /vagrant/app/config/config.prod.json
sed -i -e "s/__DB_PASSWORD__/$DB_PASSWD/" /vagrant/app/config/config.prod.json

cd /vagrant
vendor/bin/doctrine orm:schema-tool:create
cd -