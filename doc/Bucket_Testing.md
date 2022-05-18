# BucketTesting

The bucket testing feature allows us to distribute visitors in "test groups" (buckets) and 
make the Fundraising Application behave differently, depending on the bucket a visitor is in. 
It also logs the selected buckets of a visitor when an application event occurs 
(e.g. visitors donates, visitor applies for membership, etc) 

The goal of the bucket testing is to measure which application behavior change leads to an 
increase in one of our metrics (number of donations, number of non-anonymous donations, 
number of recurring donations, etc.).

We chose the name "Bucket Testing" instead of the more commonly know name "A/B testing" 
because the system is not limited to two buckets per test.

We designed the system to run bucket tests without URL parameters from banners, 
but we rarely used that functionality.

This is a reference documentation for developers, 
see [How to set up an A/B test](HOWTO_Create_an_a_b_test.md) 
for practical steps for setting up campaigns

## Domain

### Bucket
Each bucket has a *name* and a reference to the *campaign* that it belongs to.
The name must be unique for that campaign.

Each bucket can generate a **Bucket ID**, consisting of the prefix `campaign`,
the campaign name and the bucket name, separated by dots.

Class: `WMDE\Fundraising\Frontend\BucketTesting\Domain\Model\Bucket`

### Campaigns
Campaigns are a collection of *Buckets*. One of these buckets is the **Default Bucket** - 
the one that the bucket selection algorithm will choose as a fallback 
(see "Bucket selection algorithm" below).

Each campaign has a unique *name*.
Campaigns also have properties that influence the bucket selection:

- **active** 
- **Start and End Date** 
- **URL key**
- **"URL key is mandatory"** flag

Class: `WMDE\Fundraising\Frontend\BucketTesting\Domain\Model\Campaign`

### Bucket selection algorithm

The bucket selection algorithm receives a collection of all currently configured campaigns,
and will return a list of **Buckets**, one for each campaign.

The rules for selecting a bucket for a campaign are (in this order):

* Return default bucket for inactive campaigns
* Return default bucket for campaigns with a date range (start and end date) outside the current date.
* If a parameter name from the URL matches the URL key of the campaign, return the bucket **by index** with the parameter value (0-based).
  Example: parameter array is `['u'=>1]`, and the campaign has the URL-key `u` and has 2 Buckets, return 2nd bucket.
* If the visitor has a cookie with matching URL parameter, return the bucket by index of the parameter value.
* Return default bucket if URL key is mandatory and not present in URL. This is for campaigns that are "triggered" by fundraising banners on wikipedia.
* Return random bucket

Class: `WMDE\Fundraising\Frontend\BucketTesting\BucketSelector`

### Bucket Log

The bucket log is for storing an application event (e.g. making a donation) 
with the buckets of the current visitor. We convert the `Bucket` into a `BucketLogBucket`, 
that contains the name of the bucket and the campaign and has a reference to the `BucketLog`,
which contains the event name.

Classes:

* `WMDE\Fundraising\Frontend\BucketTesting\Domain\Model\BucketLog`
* `WMDE\Fundraising\Frontend\BucketTesting\Domain\Model\BucketLogBucket`

## Architecture

### Building the campaign collection
For the bucket selection, we need to build the collection of campaigns from a configuration file.
We're validating the file structure with a [Symfony configuration definition](https://symfony.com/doc/current/components/config/definition.html).

The configuration is in `app/config/campaigns.yml`,
with overrides for testing and development in `campaigns.dev.yml`. 
The override file contains only the definitions that are *different* from the main file,
e.g. start dates, end dates and active flag.  

Classes:

* `WMDE\Fundraising\Frontend\BucketTesting\CampaignConfiguration` 
* `WMDE\Fundraising\Frontend\BucketTesting\CampaignConfigurationLoader`

### HTTP request integration

We're use a [middleware](https://symfony.com/doc/current/event_dispatcher/before_after_filters.html)
to select the buckets for all campaigns on each HTTP request 
(based on URL parameters and a cookie in the request) and to store all bucket IDs in a cookie on response.

Classes: 

* `WMDE\Fundraising\Frontend\App\EventHandlers\StoreBucketSelection`
* `WMDE\Fundraising\Frontend\App\EventHandlers\BucketLoggingConsentHandler`

### Logging

Some of our use cases emit **Domain events** on the event bus of the application. 
The `BucketLoggingHandler` subscribes to the events and logs. 
It also tracks if the visitor has previously given consent to 
the tracking and only tracks with consent

Classes:

* `WMDE\Fundraising\Frontend\App\EventHandlers\BucketLoggingConsentHandler`
* `WMDE\Fundraising\Frontend\Infrastructure\EventHandling\DomainEventHandler\BucketLoggingHandler`
* `WMDE\Fundraising\Frontend\Infrastructure\EventHandling\EventDispatcher`

### Selecting the behavior, based on the visitor's buckets

We have two places where we decide on application behavior, based on the buckets the visitor is in:
The PHP code of the fundraising application and the client-side code of the fundraising application.

Both selections use the **Bucket IDs** (see "Buckets" section above) to create a custom behavior for each bucket.

#### Server-side behavior

We wanted to avoid to scattered `if` statements across our code.
To achieve that, we have bundled that branching logic in the `ChoiceFactory`, 
which produces class instances depending on campaign settings.

Currently, the `ChoiceFactory` uses the [doorkeeper](https://github.com/remotelyliving/doorkeeper)
feature toggle library. The library contains decision-making logic based on flags and date ranges.
We're building the feature-toggle configuration from the campaign configuration.
We could remove the feature toggle library, since it duplicates the bucket selection logic.

Classes:

* `WMDE\Fundraising\Frontend\BucketTesting\FeatureToggle`
* `WMDE\Fundraising\Frontend\Factories\ChoiceFactory`
* `WMDE\Fundraising\Frontend\BucketTesting\CampaignFeatureBuilder`
* `WMDE\Fundraising\Frontend\BucketTesting\DoorkeeperFeatureToggle`


#### Client-side

We are passing the selected bucket IDs to the client as application data.
We are using the `<feature-toggle>` component that allows us to specify 
in our markup which component to render, based on the selected buckets.

We consider `if`-statements checking the bucket IDs bad practice and avoid them, 
using `<feature-toggle>` wherever possible.


