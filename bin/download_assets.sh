#!/bin/bash

ASSET_BRANCH=${1:-main}
TMP=$(mktemp -d)
echo "Downloading assets ..."
curl -sSL "https://gitlab.com/api/v4/projects/fun-tech%2ffundraising-app-frontend/jobs/artifacts/$ASSET_BRANCH/download?job=build-artifacts" > $TMP/assets.zip

echo "... removing old assets ..."
rm -rf web/skins/dist web/skins/laika

echo "... extracting assets ..."
unzip -q $TMP/assets.zip -d web/skins -x "*.html"
mv web/skins/dist web/skins/laika

echo "... cleanup ..."
rm -rf $TMP

echo "... finished."
