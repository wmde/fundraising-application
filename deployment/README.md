# Deployment

This directory contains the deployment files for the code of the fundraising frontend application.

## Prerequisites on the Server
The application requires the following infrastructure to be set up already on the server:

- Git
- PHP 7 (with intl and kontocheck extension)
- Composer
- A web server with the correct document root and PHP configuration
- A MySQL database server and database
- SSH access for the deployment user

Ansible scripts that set up the infrastructure are at the private Wikimedia Germany fundraising infrastructure GitHub repository.  

On the machine that does the deployment machine, you need to have [Ansible](http://ansible.com/) and Node.js to be set installed.

## Deploy the application

All commands assume you're in the `deployment` directoy, but you can change paths as needed.

As a prerequisite you need to create a file with the server variables in the `inventory` directory. 
Copy the example file called `production_example` and tailor it to your needs. You need three files, one for test, one for staging and one for production.
For security reasons the contents of the `inventory` directory are not in the Git repository. 

To deploy, run the following command:

    ansible-playbook -i inventory/test deployment.yml

`inventory/test` is the path to your server variables file.

To deploy a different branch than `master`, run the following command:

    ansible-playbook -i inventory/test --extra-vars 'deploy_branch=test' deployment.yml

### Creating the application configuration file

If you run the deployment script for the first time, it will fail after checking out the code, because the configuration file is missing (it's not in the repository for security reasons).
You then have to log in once and create the file `app/config/config.prod.json` with the correct database, MCP, Paypal and content wiki credentials.
You only have to do this once.

## Test if the deployment works in all environments
There is a Vagrant configuration for setting up three servers for test, staging and production in `deployment/test/Vagrantfile`. The LEMP stack for the three machines can be installed by running the Ansible playbook from the repository https://github.com/gbirke/wikimedia-fundraising-devbox and the inventory file `deployment/test/inventory`. When the machines are ready, the deployment can be tested with

    ansible-playbook -i test/inventory all.yml
