# How to change I18N content for a feature branch

Our continuous delivery system immediately deploys all changes to the
`test` and `production` branches of the
`wmde/fundraising-frontend-content` repository. If you are developing a
new feature where you need to edit or delete content, your content changes
will break the currently deployed applications as long as your branch is
not deployed.

## Why is the content deployed independently from the code?

The current situation is the result of three requirements:

1. We want to make it easy for the Fundraising Department to change
   content without needing to contact Fundraising Tech Team for
   deployment. GitHub was a good solution because it provides different
   branches for the different systems, an editor and user management.
2. We want the changes of the Fundraising Department to appear
   immediately, so they can see how they will look.
3. We want to control when we deploy new features and bug fixes (adhering
   to our development and QA process), so code deployments are not
   automated but manual.

We track ideas and progress for improvements of the current process in
https://phabricator.wikimedia.org/T210094

## How can I then develop new features?

You have two options to make those changes safely (Approach 2 being the
preferred way)

### Approach 1: Temporarily duplicate keys

You still work on the `test` branch. You don't delete keys and instead of
editing existing keys, you add new ones with a prefix or suffix. After you
deployed your feature to production, you create two new commits:
One in the application repository, changing the keys to the desired state,
one in the content repository, deleting unused keys and deleting the keys
with suffix/prefix.

You MUST deploy the changes in that branch as fast as possible, to keep
the time frame where the code is out of sync with the content as small as
possible.

### Approach 2: Feature branch for development

If you want to see translations quickly while developing on your machine,
keeping the test and production environments unaffected, create a branch
in the content repository and change the branch name in the
`composer.json` of the application repository.

You MUST make sure that you merge the feature branch after you have
deployed, for the changes to appear.

