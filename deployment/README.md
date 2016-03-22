# Deployment

This directory contains the deployment files for the fundraising frontend application itself.

## Prerequisites
The application requires the following infrastructure to be set up already on the server:

- Git
- PHP 7
- Composer
- A web server with the correct document root and PHP configuration
- A MySQL database server and database
- SSH access for the deployment user

It also requires [Ansible](http://ansible.com/) and Node.js to be set up on the machine that does the deployment.


## Deploy the application

If you're in the `deployment` directory, run the following command:

    ansible-playbook -i inventory/test deployment.yml

To deploy a different branch than `master`, run the following command:

    ansible-playbook -i inventory/test --extra-vars 'deploy_branch=test' deployment.yml

To deploy to a different environment than `test`, run the following command:

    ansible-playbook -i inventory/production deployment.yml


## Test if the deployment works in all environments
There is a Vagrant configuration for setting up three servers for test, staging and production in `deployment/test/Vagrantfile`. The LEMP stack for the three machines can be installed by running the Ansible playbook from the repository https://github.com/gbirke/wikimedia-fundraising-devbox and the inventory file `deployment/test/inventory`. When the machines are ready, the deployment can be tested with

    ansible-playbook -i test/inventory all.yml
