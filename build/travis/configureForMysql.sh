set -ex

mysql -e 'create database spenden;'
cp build/travis/mysql-test-config.json app/config/config.test.local.json
