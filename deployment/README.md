# Deployment

This directory contains the Ansible deployment files for the code of the fundraising application.

We split the deployment steps in two phases - the "build" phase, and the "deploy" phase. 

The "build" phase happens on the "local" machine (the machine that runs the playbook). It checks out the code, 
downloads PHP and Javascript dependencies, builds the Frontend assets and bundles everything into an archive.  

The "deploy" phase copies the archive to the server and makes sure all necessary paths exist.

## Directory structure on the server

```
/local/sites
+-- spenden.wikimedia.de
    +-- release-20200102120115
    +-- release-20200202130115
    +-- fundraising-frontent-content
        +-- 20200102120115
        +-- 20200202130115
        +-- current
    +-- logs
    +-- htdocs
```

All deployments are in `/local/sites`, followed by the domain name (variable `domain` in the inventory file). 
This is the **Application Directory**. It contains the latest releases (directories starting with `release-` or 
a directory called `current-release` depending on the deploy mode, see below), the `logs` directory that contains the 
web server, PHP and application log files, the `fundraising-frontent-content` directory with atomic releases of the 
I18N content (messages, email text, images, etc) and the `htdocs` directory which is a symbolic link to the `web` 
directory of the current release. `htdocs` is the configured "Document Root" of the web server for that domain. 

## Deploy modes - atomic or overwrite 
There are two "modes" of deployment - "atomic" and "overwrite". 

"Atomic" is the default mode. "Atomic" means that the playbook creates a new unique directory for each run of the playbook, extracting the 
archive into the unique directory. When all files are ready, the playbook changes the symlink from the previous unique 
directory to the freshly deployed one. While the deployment playbook is still running, the web server serves the previous 
application. When the symlink changes, the web server serves the new application.  
Use this deployment mode to deploy to production. 


"Overwrite" means that the playbook will delete the target directory before 
extracting the archive and creating the paths. During that time, the application is broken.  
Use this deployment mode to deploy feature branches to test servers. 

To trigger overwrite mode, set the variables `overwrite_mode` or `test_branch` 
when calling the playbook. See the next 2 sections for an explanation of the difference.

## The testing pool

For testing out feature branches, we have a "testing pool" - a number of test domains on a web server. 
When deploying a new feature branch, we want to use the domain that was least recently deployed to. 
When deploying the same feature branch again, we want to re-use the domain. We have built the 
[command-line tool `get_deploy_host`](https://github.com/wmde/fundraising-deploy-hosts) that fetches
and updates the relation between domain and name and feature branch in a JSON file. 
The deployment playbook calls `get_deploy_host` if you run the deployment playbook with the `test_branch` variable. 
This will also trigger the "overwrite" deploy mode automatically (see the previous section).

By default, all test pool installations share the same `log` and `fundraising-frontent-content` with the original, 
non-dynamic `domain` application directory from the config file, usually `test-spenden-2.wikimedia.de`. 
If you want individual directories for your test branch deploy, set `domain` to `None`.  

If you are testing functionality of external payment providers, you should *not* use the testing pool. 
This is because the test environments of the external payment providers use the same test domain, 
https://test-spenden-2.wikimedia.de and changing the domain in the payment provider configuration is tedious.
If you want to deploy to the regular test domain, you can use either atomic deploy mode or set `overwrite_mode=1` 
instead of setting `test_branch`.

## Example Ansible commands

All commands assume you're in the `deployment` directory, but you can change paths as needed.

To deploy, run the following command:

    ansible-playbook -i inventory/test deployment.yml

`inventory/test` is the path to your inventory file. Use a different inventory file to deploy to a different environment.

To deploy a branch to the testing pool in overwrite mode run the following command:

    ansible-playbook -i inventory/test --extra-vars 'test_branch=test' deployment.yml

To deploy a branch to the test server in overwrite mode, without using the testing pool, run the following command:

    ansible-playbook -i inventory/test --extra-vars 'overwrite_mode=1' deployment.yml

To deploy a different branch than `master` in "atomic" mode, run the following command:

    ansible-playbook -i inventory/test --extra-vars 'build_branch=test' deployment.yml
    
To deploy with a different environment (`dev` instead of `prod`), than the one configured in the inventory, run the following command:

    ansible-playbook -i inventory/test --extra-vars 'environment_name=dev' deployment.yml

## Rolling back atomic deployments
In "Atomic" deploy mode, the web server delivers the old version of the application while the deployment playbook is running.
The last step of the deployment playbook activates the new version.

The deployment playbook accomplishes this with the following steps during the different stages of the deployment process:

 - At the start of the process, create a new directory with a timestamp in its name, e.g. `release-20160816120255`.
 - At the end of the deployment process, change the symbolic link to the new directory.
 - If everything went well, delete the oldest `release-XXX` directory, keeping only the last 5 deployments on the server.
   The number of kept deployments can be adjusted by changing the variable `keep_releases` in the file `deployment/deployment.yml`

To roll back ("undo") a deployment, log in to the server and change the symlink to a previous deployment.

## Deploying a release that needs database changes

Database changes use [Doctrine migration scripts](https://www.doctrine-project.org/projects/doctrine-migrations/en/2.2/reference/introduction.html), which are part of the application.

Until we use Doctrine Migrations 3.0 , each bounded context has its own migration configuration file, in `app/config/migrations`. In the examples, replace `migration_config.yml` with the name of the bounded context migration you want to run. 

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
Login to the server of the fundraising application (test server or production server, depending on which version you want to deploy).
In `/usr/share/ngix/www/` you can find the directory named after the domain of the server you want to migrate. The directory contains the the last 5 releases, each in its own directory.
Change into the new release directory (the timestamped directory, *not* the `html`).

Run the `migrate` command, replacing the `migration_config.yml` file with your own file name:

    vendor/bin/doctrine-migrations migrations:migrate --configuration=app/config/migrations/migration_config.yml

If you want to run migrations from more than one bounded context, you have to run the command multiple times, with a different configuration file!

### 4. Change the symlink

Change to the directory containing the releases. Change the symlink to the release you noted in step 1 (replacing the `TIMESTAMP` placeholder):

    rm html && ln -s release-TIMESTAMP html

### 5. Deactivate Maintenance mode (if active)
If you put the application into maintenance mode, you need to activate the fundraising application again.

Use the maintenance mode Ansible playbook (not part of this Git repository but in the private Wikimedia Germany fundraising infrastructure GitHub repository): 

    ansible-playbook -i inventory/servers.ini -b --ask-become-pass --extra-vars "maintenance_mode_status='Off'"  maintenance_mode.yml

## Preparing a new server for deployment

### Prerequisites on the server
The application requires the following infrastructure to be set up already on the server:

- PHP 7 with the following extensions active:
    - curl
    - intl
    - JSON
    - [kontocheck extension](http://kontocheck.sourceforge.net/)
    - PDO
    - PDO Mysql
    - Sodium
- A web server with the correct document root (`htdocs` in the application directory) and PHP configuration 
  (`log_errors=On` and `error_log` pointing to the `logs` directory in the application directory ). The web server 
  should redirect all requests which don't resolve to files to index.php so the application can handle routes.
- A MySQL compatible database server (at least version 5.6) and database
- SSH access for the deployment user. All files created by the web server must be writable (i.e. deletable) by the deployment user. 
  This can be done by setting the umask for the web server (mod_umask for Apache) or PHP-FPM. 

### Prerequisites on the deployment machine
On the machine that does the deployment, you need to have Docker, Git and [Ansible](http://ansible.com/) installed. 

For each domain you want to deploy to, you need an *inventory file* and *configuration file*. 
You must decide which application environment (`dev`, `uat` or `prod`) you want to run. 
In the following commands, replace the `<ENVIRONMENT>` placeholder with the environment name. 

### Create inventory files on the deployment machine
Inventory files contain server/environment specific information.

Duplicate the example file `deployment/inventory/production_example` and set server names and file paths (as determined by the server setup). 
You need a separate file for each domain you want to deploy to, e.g. test and production.

Set the `environment_name` value to `<ENVIRONMENT>` (see above).

For security reasons the contents of the `inventory` directory are not in the Git repository, except for the example. 
**Do not check in your inventory files.**

### Create a configuration file on the deployment machine

Log in to the deployment machine and in the configuration directory, create a new directory for the domain name.

In the new directory create the file `config.<ENVIRONMENT>.json` with all the necessary application data. 
You need to configure the credentials for the database, MCP and Paypal. 
You don't need to change values that are similar to values in `app/config/config.dist.json`.

Keep the configuration files in a local Git repository, so you can commit and push your changes.
