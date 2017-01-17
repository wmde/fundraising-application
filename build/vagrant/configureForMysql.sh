#!/bin/bash -ex

mysql -pPASSWORD_HERE -u root -e 'CREATE DATABASE fundraising;'
mysql -pPASSWORD_HERE -u root -e "CREATE USER 'fundraising'@'localhost' IDENTIFIED BY 'INSECURE PASSWORD';"
mysql -pPASSWORD_HERE -u root -e "GRANT ALL ON fundraising.* TO 'fundraising'@'localhost';"

cp /vagrant/build/vagrant/config.prod.json /vagrant/app/config/config.prod.json

/vagrant/vendor/bin/doctrine orm:schema-tool:create