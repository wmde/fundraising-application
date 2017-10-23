#!/bin/bash

cd /vagrant

composer install --no-interaction

# make content resources accessible via web server
ln -fs ../vendor/wmde/fundraising-frontend-content/resources web/resources

npm install
npm run build-assets
npm run copy-assets

mkdir -p var/cache
mkdir -p var/log

cd -
