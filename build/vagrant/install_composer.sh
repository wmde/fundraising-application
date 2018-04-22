#!/bin/sh
set -e

EXPECTED_SIGNATURE=$(wget -q -O - https://composer.github.io/installer.sig)
php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
function cleanup {
    rm composer-setup.php
}
trap cleanup EXIT

ACTUAL_SIGNATURE=$(php -r "echo hash_file('SHA384', 'composer-setup.php');")

if [ "$EXPECTED_SIGNATURE" != "$ACTUAL_SIGNATURE" ]
then
    >&2 echo 'ERROR: Invalid installer signature'
    exit 1
fi

php composer-setup.php --quiet
RESULT=$?

mv composer.phar /usr/local/bin/composer

exit $RESULT
