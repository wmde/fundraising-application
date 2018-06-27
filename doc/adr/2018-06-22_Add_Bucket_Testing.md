# Add [Bucket Testing](https://en.wikipedia.org/wiki/A/B_testing) to all parts of the application

## Status
Accepted

## Context
From time to time the Fundraising team wants to try out changes in the fundraising frontend to see if they improve the amount of donors, the donation sum, the membership application rate and so on.

In the past, the developers implemented those experiments in an ad-hoc fashion, with different places in the code implementing the branching, and with different places in the code and database to store the outcomes.

From a developer perspective, the new implementation of bucket testing must not affect the code quality and spread throughout the code base. To achieve that, we should collect the relevant code in one agreed- upon central location. Also, there should be some mechanism in place to determine, if experiments are still ongoing. Ideally, our CI should help us to prune dead code from time to time.

When our system places a visitor in a bucket, it must store that assignment persistently in the database. When querying the database, we must be able to link the record to donations/memberships if needed. No need to build an analysis software yet: The FUN team will provide the results of each bucket testing campaign as raw/aggregated data obtained though querying the database.

## Decision
* Create campaign configuration, that describes the experiments in a human-readable (YAML) with bucket names, start and end dates and contextual information.
* Add code that translates the configuration into feature toggles. Use [Doorkeeper](https://github.com/remotelyliving/doorkeeper) as a feature toggle library. We [evaluated different feature toggle libraries](https://gist.github.com/gbirke/ab53316c69341718a9dd5cb79ed32642) and chose Doorkeeper because
	* It has the most modern code (PHP 7), 100% test coverage and the code looks most SOLID of the considered options.
	* It already implements toggle conditions we need (check date range, check user bucket).
* All branching based on feature toggles *must* take place at creation time, i.e. in the central factory that creates the use cases and their dependencies. We created the `ChoiceFactory` for that purpose.

Removing the old code that stores the result of old A/B tests (but was not used in the last 2 campaigns and stores empty default values) is not as part of the acceptance criteria.

## Consequences

The fundraising team can now experiment with all parts of the application. Examples of possible experiments are:

* cosmetic changes to templates
* trying out different skins
* try out different functionality on the client side Javascript
* add and remove functionality like payment types or user flow

In the future, the Doorkeeper library will help us to always be ready deploy the current master to production by being able to turn features on and off based on environment.
