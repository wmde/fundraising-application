#!/usr/bin/env bash

if [ "$MCP_ACCESS_KEY" != "" ] && [ "$MCP_PROJECT_ID" != "" ]; then

cat << EOF > app/config/config.test.local.json
{
	"creditcard": {
		"access-key": "$MCP_ACCESS_KEY",
		"project-id": "$MCP_PROJECT_ID"
	},
	"db": {
		"driver": "pdo_sqlite",
		"memory": true
	}
}
EOF

else

cat << EOF > app/config/config.test.local.json
{
	"db": {
		"driver": "pdo_sqlite",
		"memory": true
	}
}
EOF

fi