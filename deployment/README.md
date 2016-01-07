# Deployment

This directory contains the deployment files for the fundraising frontend application itself.

## Prerequisites
The application requires the following infrastructure to be set up already on the server:

- PHP 7
- Composer
- A web server with the correct document root and php configuration
- A MySQL database server and database
- SSH access

It also requires [Ansible](http://ansible.com/) to be set up on the machine that does the deployment.

## Setting up the inventory file
Create a file named `inventory` with server data that looks like this:

    deploytest.yourdomain.com deploy_dir=/var/www/fundraising2

Note: When the server from the 1st step of ticket https://phabricator.wikimedia.org/T123049 is done, the inventory file will be included in this repository.

## Deploy the application

If you're in the `deployment` directory, run the following command:

    ansible-playbook -i inventory playbook.yml

To deploy a different branch than `master`, you can use the following command:

    ansible-playbook -i inventory --extra-vars 'deploy_branch=test' playbook.yml
