# How to run the DRONE CI pipeline on your local machine

## Prerequisites

You need to have the `drone` command line interface installed on your
local machine. See https://docs.drone.io/cli/install/ for download links
and installation instructions.

You need to have the `pass` password manager installed, configured and be
able to access the Fundraising Tech password repository. You can try out
your setup by running `pass wmde-fun/github-composer-ci-token`. After
entering your GPG password you should then see the GitHub token.

As the last item on the list, you should be able to use `docker` commands
on your local machine.

## Running Drone CI pipeline on your local machine


    make drone-ci


