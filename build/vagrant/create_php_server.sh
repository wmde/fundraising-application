#!/usr/bin/env bash

cp fundraising_app.service /etc/systemd/system/
chmod 664 /etc/systemd/system/fundraising_app.service

systemctl daemon-reload
systemctl start fundraising_app.service