# How to create an A/B test for a feature

## 1. Create a campaign definition
Edit the file `app/config/campaigns.yml` and add a new entry. If you're unsure about the end date, set it to the end of 
next year. If you want to start with a deactivated campaign, but want to test it in your local environment, copy the 
campaign definition to the file `app/config/campaigns.local.yml` and use different settings for `start`, `end` and `active`.

The campaign name must be unique inside the campaigns configuration, the bucket names may overlap between campaigns, 
but that does not mean the user will be placed in the same bucket for two different campaign.

Please check that:
* The campaign name is unique in the campaign file.
* The url key is set and is unique in the campaign file.
* The start and end dates must be quoted and in the format `YYYY-MM-DD` or `YYYY-MM-DD H:i:s`. All dates are considered to be in the Europe/Berlin timezone.
* The end date must be after the start date.
* The `default_bucket` must be part of the `buckets` definition.
* Bucket names beginning with digits (e.g. `10h16`) must be quoted, both for `buckets` and `default_bucket`.

### Example Campaign:

```yaml
header_template:
  description: Test different header and menu designs
  reference: "https://phabricator.wikimedia.org/T196337"
  url_key: h
  start: "2018-10-01"
  end: "2018-12-31"
  active: true
  buckets:
    - default_header
    - fancy_header
  default_bucket: default_header
``` 

## 2. Create a factory method for the campaign

Edit the file `src/Factories/ChoiceFactory.php` and add a factory method. Inside the method you must call `$this->featureToggle->isFeatureActive`
for each bucket in the campaign. The parameter for `isFeatureActive` must follow the pattern `campaigns.<CAMPAIGN_NAME>.<GROUP_NAME>`.

The factory method should get all its dependencies via parameter. It must have a defined return type. You may return 

* different instances of the same class, 
* different implementations of the same interface,
* scalar values (use with caution, returning a class is preferable almost always)   

Please check that:
* `isFeatureActive` is called for *every* bucket of the campaign.
* The feature for the default bucket is checked *last*, because the default bucket is always active, regardless of campaign state.
* Throw `UnknownChoiceDefinition` with a proper error message after checking all buckets. This is an additional safeguard 
against changed or misspelled campaign and bucket names. It is a case of "this should never happen" if the campaign 
file is validated by the CI.

### Example implementation - parameterized instance

```php
public function getHeaderTemplate( Twig_Environment $twig ): TwigTemplate {
	if ( $this->featureToggle->isFeatureActive( 'campaigns.header_template.fancy_header' ) {
		return new TwigTemplate( $twig, 'Fancy_Header.html.twig' );
	} elseif ( $this->featureToggle->isFeatureActive( 'campaigns.header_template.default_header' ) {
		return new TwigTemplate( $twig, 'Header.html.twig' );
	}
	throw new UnknownChoiceDefinition( 'Failed to determine header template' );
}
```

### Example implementation - different implementations

```php
public function getHeaderTemplate( Twig_Environment $twig ): HeaderTemplateInterface {
	if ( $this->featureToggle->isFeatureActive( 'campaigns.header_template.fancy_header' ) {
		return new FancyHeaderTemplate( $twig );
	} elseif ( $this->featureToggle->isFeatureActive( 'campaigns.header_template.default_header' ) {
		return new HeaderTemplate( $twig );
	}
	throw new UnknownChoiceDefinition( 'Failed to determine header template' );
}
```

## 3. Inject the created instance in the main factory

Edit the file `src/Factories/FunFunFactory.php` to use the instance returned by `ChoiceFactory`.

```php
private function newPresenter(): Presenter {
	return new Presenter(
		$this->getChoiceFactory()->getHeaderTemplate( $this->getTwig() )
	);
} 
```

## 4. Add unit tests to the route tests that are affected

### Testing Routes in Edge-to-Edge tests

Edge-To-Edge test should load the original configuration files, but with deactivated campaigns (so your tests don't become date-dependent).
For ensuring an always deactivated campaigns, put an an override entry like this for every campaign into the file `app/config/campaigns.test.yml`:

```yaml
header_template:
	active: false
```

The settings in `app/config/campaigns.test.yml` will be merged with the settings in `app/config/campaigns.yml`.

Deactivating the campaigns also ensures that you always test the default path. 
If you want to test other buckets in the campaign, you need to explicitly set a different default bucket by overriding 
the campaign loader:

```yaml
public function testFancyHeader() {
	$this->createEnvironment( [], function ( Client $client, FunFunFactory $factory ): void {
		$factory->setCampaignConfigurationLoader( new OverridingCampaignConfigurationLoader(
			$factory->getCampaignConfigurationLoader(),
			[ 'confirmation_pages' => [ 'default_bucket' => 'fancy_header' ] ]
		) );

		$crawler = $client->request( 'GET', 'some-route-name' );

		this->assertCount( 1, $crawler->filter( 'head.fancy-schmancy' ) )
	} );
}
```
