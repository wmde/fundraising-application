#!/usr/bin/env bash

# Create VHost
cp /vagrant/build/vagrant/fundraising_nginx.conf /etc/nginx/sites-available/fundraising
ln -s /etc/nginx/sites-available/fundraising /etc/nginx/sites-enabled/fundraising

# Configure PHP-FPM
cp /vagrant/build/vagrant/php-fpm.www.conf /etc/php/7.1/fpm/pool.d/www.conf

systemctl reload nginx
systemctl restart php7.1-fpm

# Configure app
if [ ! -f /vagrant/app/config/config.prod.json ]; then
    cp /vagrant/build/vagrant/config.prod.json /vagrant/app/config/config.prod.json
fi

# Log all outgoing mails instead of sending them
echo "cat >> /tmp/logmail.log" > /usr/local/bin/logmail
chmod a+x /usr/local/bin/logmail
sed -i -e "s/\(;\s*\)\?sendmail_path\s*=.*/sendmail_path=\/usr\/local\/bin\/logmail/" /etc/php/7.1/cli/php.ini