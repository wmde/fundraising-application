#!/usr/bin/env bash

# Create systemd service
cp /vagrant/build/vagrant/fundraising_app.service /etc/systemd/system/
chmod 664 /etc/systemd/system/fundraising_app.service

systemctl daemon-reload
systemctl start fundraising_app.service

# Configure app
if [ ! -f /vagrant/app/config/config.prod.json ]; then
    cp /vagrant/build/vagrant/config.prod.json /vagrant/app/config/config.prod.json
fi

# Log all outgoing mails instead of sending them
echo "cat >> /tmp/logmail.log" > /usr/local/bin/logmail
chmod a+x /usr/local/bin/logmail
sed -i -e "s/\(;\s*\)\?sendmail_path\s*=.*/sendmail_path=\/usr\/local\/bin\/logmail/" /etc/php/7.1/cli/php.ini