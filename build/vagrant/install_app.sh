#!/bin/bash

cd /vagrant
composer install --no-interaction
npm install
npm run build-js

mkdir -p var/cache
mkdir -p var/log

cd -
