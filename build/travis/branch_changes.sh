#!/bin/bash
export CHANGES_IN_PHP=$(git diff --name-only master | grep -cE '^(composer|app/config)|(php|twig)$')
export CHANGES_IN_JS=$(git diff --name-only master | grep -cE '^(app/config|skins/laika/package)|\.(vue|js|ts|css|scss)$')

