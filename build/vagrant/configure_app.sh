#!/usr/bin/env bash

#Create systemd service
cp /vagrant/build/vagrant/fundraising_app.service /etc/systemd/system/
chmod 664 /etc/systemd/system/fundraising_app.service

systemctl daemon-reload
systemctl start fundraising_app.service

# Configure app
if [[ -z "$WIKI_PASSWD" ]]; then
    WIKI_PASSWD="WIKI PASSWORD MISSING!!!"
fi
cp /vagrant/build/vagrant/config.prod.json /vagrant/app/config/config.prod.json
sed -i -e "s/__WIKI_PASSWORD__/$WIKI_PASSWD/" /vagrant/app/config/config.prod.json
