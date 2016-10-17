# Deployment

This directory contains the Ansible deployment files for the code of the fundraising frontend application.

The deployment scripts are structured in two phases - building the archive and all its PHP and Javascript dependencies on the "local" 
machine (the machine that runs the playbook) and then deploying the archive to the server. The deployment is also made 
in fashion that the old version will run until the new version is fully deployed and then the switch is made.   

## Deploy the application

All commands assume you're in the `deployment` directory, but you can change paths as needed.

To deploy, run the following command:

    ansible-playbook -i inventory/test deployment.yml

`inventory/test` is the path to your inventory file. Use a different inventory file to deploy to a different environment.

To deploy a different branch than `master`, run the following command:

    ansible-playbook -i inventory/test --extra-vars 'deploy_branch=test' deployment.yml

## Rolling back deployments and understanding atomic deploys
Each deployment is "atomic", that means that while the deployment script is still running, the web server delivers the old version of the application. The last step of the deployment script is activating the new version.

The deployment script accomplishes this with the following steps during the different stages of the deployment process:

 - At the start of the process, create a new directory with a timestamp in its name, e.g. `release-20160816120255`.
 - At the end of the deployment process, change the symbolic link to the new directory.
 - If everything went well, delete the oldest `release-XXX` directory, keeping only the last 5 deployments on the server. The number of kept deployments can be adjusted by changing the variable `keep_releases` in the file `deployment/deployment.yml`

To roll back ("undo") a deployment, log in to the server, change the symlink to a previous deployment and clear the PHP-FPM opcache with the following command:

    php cachetool.phar opcache:reset --fcgi=/var/run/php/php7.0-fpm.sock

## Preparing a new server for deployment

### Prerequisites on the Server
The application requires the following infrastructure to be set up already on the server:

- PHP 7 (with intl and [kontocheck extension](http://kontocheck.sourceforge.net/))
- A web server with the correct document root and PHP configuration
- A MySQL database server and database
- SSH access for the deployment user

Ansible scripts that set up this infrastructure are at the Wikimedia Germany fundraising infrastructure GitHub repository (private).  

On the machine that does the deployment, you need to have Git, [Ansible](http://ansible.com/), PHP 7 and Node.js to be installed.

### Create the shared directories and application configuration file on the server

Log in to the server and create the following directories:

 - **`logs`** - for the application logs (can be identical to web server log, must be writable by PHP-FPM process)
 - **`shared/config`** -  for the configuration file

 Create the file `shared/config/config.prod.json` with all the necessary application data. You need to configure the credentials for the database, MCP, Paypal and the content wiki. You don't need to change values that are similar to values in `app/config/config.dist.json`.

### Create inventory files on the deployment machine
Inventory files contain server/environment specific information.

Duplicate the example file `deployment/inventory/production_example` and set server names and file paths (as determined by the server setup). You need three files, one for test, one for staging and one for production.

For security reasons the contents of the `inventory` directory are not in the Git repository. **Do not check in your configurations.**

## Test if the deployment works in all environments
There is a Vagrant configuration for setting up three servers for test, staging and production in `deployment/test/Vagrantfile`. The LEMP stack for the three machines can be installed by running the Ansible playbook from the repository https://github.com/gbirke/wikimedia-fundraising-devbox and the inventory file `deployment/test/inventory`. When the machines are ready, the deployment can be tested with

    ansible-playbook -i test/inventory deployment.yml
