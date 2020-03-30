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

    ansible-playbook -i inventory/test --extra-vars 'build_branch=test' deployment.yml
    
To deploy with a different environment (`dev` instead of `prod`), run the following command:

    ansible-playbook -i inventory/test --extra-vars 'environment_name=dev' deployment.yml

## Rolling back deployments and understanding atomic deploys
Each deployment is "atomic", that means that while the deployment script is still running, the web server delivers the old version of the application. The last step of the deployment script is activating the new version.

The deployment script accomplishes this with the following steps during the different stages of the deployment process:

 - At the start of the process, create a new directory with a timestamp in its name, e.g. `release-20160816120255`.
 - At the end of the deployment process, change the symbolic link to the new directory.
 - If everything went well, delete the oldest `release-XXX` directory, keeping only the last 5 deployments on the server. The number of kept deployments can be adjusted by changing the variable `keep_releases` in the file `deployment/deployment.yml`

To roll back ("undo") a deployment, log in to the server, change the symlink to a previous deployment and clear the PHP-FPM opcache with the following command:

    php cachetool.phar opcache:reset --fcgi=/var/run/php/php-fpm-current.sock

## Deploying a release that needs database changes

Database changes take the form of [Doctrine migration scripts](https://www.doctrine-project.org/projects/doctrine-migrations/en/2.2/reference/introduction.html), which are part of the application.

### 1. Deploy activating new version
Call the playbook (in this case for the test server) with the `skip_symlink` variable:

    ansible-playbook -i inventory/test --extra-vars 'skip_symlink=1' deployment.yml

This won't change the `html` symlink that links to the current version. 

Write down the release name, e.g. `release-201908161015`.

### 2. Activate Maintenance mode (if needed)
If your migration scripts take longer than a few seconds to run, you must put the application into maintenance mode, taking the application offline. Please coordinate the right maintenance period with the product manager and Fundraising OPs.

Use the maintenance mode Ansible playbook (not part of this Git repository but in the private Wikimedia Germany fundraising infrastructure GitHub repository) to display a static HTMl page instead of the Fundraising Application: 

    ansible-playbook -i inventory/servers.ini -b --ask-become-pass --extra-vars "maintenance_mode_status='On'"  maintenance_mode.yml

You can add the parameters `-l fundraising_frontend_test` or `-l fundraising_frontend_production` to put only one environment into maintenance mode.

The contents of the maintenance mode files are stored at https://github.com/wmde/fundraising-maintenance

### 3. Run migration scripts
Login to the server of the fundraising application (test or production), change to the new release directory (the timestamped directory, *not* the `html`).

Before running the migration script you have to set the application environment variable that will be used in the Doctrine `cli-config.php` configuration file. You can find the name of the application environment in the `environment_name` variable of the deployment inventory. Common names are `uat` (User Acceptance Testing) and `prod` (Production).

    export APP_ENV=prod

Check the migrations directory of FundraisingStore (`wmde/fundraising-store/migrations`) for the migration name you want to run (usually a timestamp like `20190109000000`). Then run the following command (replacing `MIGRATION_NAME` ) :

    vendor/bin/doctrine-migrations migrations:execute --configuration=vendor/wmde/fundraising-store/migrations.yml MIGRATION_NAME

### 4. Change the symlink

Change to the directory containing the relases. Change the symlink to the release you noted in step 1 (replacing the `TIMESTAMP` placeholder):

    rm html && ln -s release-TIMESTAMP html

### 5. Deactivate Maintenance mode (if active)
If you put the application into maintenance mode, you need to activate the fundraising application again.

Use the maintenance mode Ansible playbook (not part of this Git repository but in the private Wikimedia Germany fundraising infrastructure GitHub repository): 

    ansible-playbook -i inventory/servers.ini -b --ask-become-pass --extra-vars "maintenance_mode_status='Off'"  maintenance_mode.yml

## Preparing a new server for deployment

### Prerequisites on the server
The application requires the following infrastructure to be set up already on the server:

- PHP 7 (with intl and [kontocheck extension](http://kontocheck.sourceforge.net/))
- A web server with the correct document root and PHP configuration
- A MySQL database server and database
- SSH access for the deployment user

Ansible scripts that set up this infrastructure are at the Wikimedia Germany fundraising infrastructure GitHub repository (private).  

### Create the logging directories file on the server

Log in to the server and create the directory **`/usr/share/nginx/www/DOMAIN_NAME/logs`** - for the application logs (can be identical to web server log, must be writable by PHP-FPM process)

### Prerequisites on the deployment machine
On the machine that does the deployment, you need to have Docker, Git and [Ansible](http://ansible.com/) installed. 

For each domain you want to deploy to, you need an *inventory file* and *configuration file*. You must decide which application environment (`dev`, `uat` or `prod`) you want to run. In the following commands, replace the `<ENVIRONMENT>` placeholder with the environment name. 

### Create inventory files on the deployment machine
Inventory files contain server/environment specific information.

Duplicate the example file `deployment/inventory/production_example` and set server names and file paths (as determined by the server setup). You need a separate file for each domain you want to deploy to, e.g. test and production.

Set the `environment_name` value to `<ENVIRONMENT>` (see above).

For security reasons the contents of the `inventory` directory are not in the Git repository, except for the example. **Do not check in your inventory files.**

### Create a configuration file on the deployment machine

Log in to the deployment machine and in the configuration directory, create a new directory for the domain name.

In the new directory create the file `config.<ENVIRONMENT>.json` with all the necessary application data. You need to configure the credentials for the database, MCP and Paypal. You don't need to change values that are similar to values in `app/config/config.dist.json`.

The configurations are kept in a local Git repository, so you should commit and push your changes.