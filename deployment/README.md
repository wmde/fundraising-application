# Deployment

This directory contains the Ansible deployment files for the code of the fundraising frontend application.

Deployment is done using the `wmde.atomic-deploy` role in 
[Wikimedia Germany fundraising infrastructure GitHub repository (private)](https://github.com/wmde/fundraising-infrastructure).
The files in `tasks` are hooks called by this role during deployment that can be used to build the 
project (e.g. resolving PHP and Javascript dependencies).

## Deploy the application

See the documentation in the infrastructure repo.

### Prerequisites on the Server
The application requires the following infrastructure to be set up already on the server:

- PHP 7 (with intl and [kontocheck extension](http://kontocheck.sourceforge.net/))
- A web server with the correct document root and PHP configuration
- A MySQL database server and database
- SSH access for the deployment user

Ansible scripts that set up this infrastructure are at the Wikimedia Germany fundraising infrastructure GitHub repository (private).  

On the machine that does the deployment, you need to have Git, [Ansible](http://ansible.com/), PHP 7 and Node.js to be installed.

### Create the logging directories file on the server

Log in to the server and create the directory **`/usr/share/nginx/www/DOMAIN_NAME/logs`** - for the application logs (can be identical to web server log, must be writable by PHP-FPM process)

### Create inventory files on the deployment machine
Inventory files contain server/environment specific information.

Duplicate the example file `deployment/inventory/production_example` and set server names and file paths (as determined by the server setup). You need three files, one for test, one for staging and one for production.

For security reasons the contents of the `inventory` directory are not in the Git repository, except for the example. **Do not check in your inventory files.**

### Create a configuration file on the deployment machine

Log in to the deployment machine and in the configuration directory, create a new directory for the domain name.

In the new directory create the file `config.prod.json` with all the necessary application data. You need to configure the credentials for the database, MCP and Paypal. You don't need to change values that are similar to values in `app/config/config.dist.json`.

The configurations are kept in a local Git repository, so you should commit and push your changes.  

## Test if the deployment works in all environments
There is a Vagrant configuration for setting up three servers for test, staging and production in `deployment/test/Vagrantfile`. The LEMP stack for the three machines can be installed by running the Ansible playbook from the repository https://github.com/gbirke/wikimedia-fundraising-devbox and the inventory file `deployment/test/inventory`. When the machines are ready, the deployment can be tested with

    ansible-playbook -i test/inventory deployment.yml
