#!/bin/bash

cd /vagrant

composer install --no-interaction

# make content resources accessible via web server
ln -fs ../vendor/wmde/fundraising-frontend-content/resources web/resources

npm install
npm run build-js
npm run build-assets

mkdir -p var/cache
mkdir -p var/log

cd -
