# How to create an A/B test for a feature

## 1. Create a campaign definition
Edit the file `app/config/campaigns.yml` and add a new entry. If you're unsure about the end date, set it to the end of 
next year. If you want to start with a deactivated campaign, but want to test it in your local environment, copy the 
campaign definition to the file `app/config/campaigns.local.yml` and use different settings for `start`, `end` and `active`.

The campaign name must be unique inside the campaigns configuration, the group names may overlap between campaigns, 
but that does not mean the user will be placed in the same group for two different campaign.

Please check that:
* The campaign name is unique in the campaign file.
* The url key is set and is unique in the campaign file.
* The start and end dates must be quoted and in the format `YYYY-MM-DD` or `YYYY-MM-DD H:i:s`. All dates are considered to be in the Europe/Berlin timezone.
* The end date must be after the start date.
* The `default_group` must be part of the `groups` definition.
* Group names beginning with digits (e.g. `10h16`) must be quoted, both for `groups` and `default_group`.

### Example Campaign:

```yaml
header_template:
  description: Test different header and menu designs
  reference: "https://phabricator.wikimedia.org/T196337"
  url_key: h
  start: "2018-10-01"
  end: "2018-12-31"
  active: true
  groups:
    - default_header
    - fancy_header
  default_group: default_header
``` 

## 2. Create a factory method for the campaign

Edit the file `src/Factories/ChoiceFactory.php` and add a factory method. Inside the method you must call `$this->featureToggle->isFeatureActive`
for each group in the campaign. The parameter for `isFeatureActive` must follow the pattern `campaigns.<CAMPAIGN_NAME>.<GROUP_NAME>`.

The factory method should get all its dependencies via parameter. It must have a defined return type. You may return 

* different instances of the same class, 
* different implementations of the same interface,
* scalar values (use with caution, returning a class is preferable almost always)   

Please check that:
* `isFeatureActive` is called for *every* group of the campaign.
* The feature for the default group is checked *last*, because the default group is always active, regardless of campaign state.
* Throw `UnknownChoiceDefinition` with a proper error message after checking all groups. This is an additional safeguard 
against changed or misspelled campaign and group names. It is a case of "this should never happen" if the campaign 
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

Edit the appropriate files in `tests/EdgeToEdge/Routes`. Use `FunFunFactory::setFeatureToggle` to replace the 
campaign-based feature toggle with a deterministic FixedFeatureToggle to test all the code paths inside the `ChoiceFactory`.

```php

public function testDefaultHeader() {
	$this->createEnvironment( [], function ( Client $client, FunFunFactory $factory ): void {
		$factory->setFeatureToggle( $this->newDefaultFeatureToggle() );

		$crawler = $client->request( 'GET', 'some-route-name' );

		this->assertCount( 1, $crawler->filter( 'head.boring-default' ) )
	} );
}

public function testFancyHeader() {
	$this->createEnvironment( [], function ( Client $client, FunFunFactory $factory ): void {
		$factory->setFeatureToggle( $this->newAlternativeFeatureToggle() );

		$crawler = $client->request( 'GET', 'some-route-name' );

		this->assertCount( 1, $crawler->filter( 'head.fancy-schmancy' ) )
	} );
}

private function newDefaultFeatureToggle(): FeatureToggle {
		return new FixedFeatureToggle( [
			'campaigns.header_template.default_header' => true,
			'campaigns.header_template.fancy_header' => false,
		] );
	}

private function newAlternativeFeatureToggle(): FeatureToggle {
	return new FixedFeatureToggle( [
			'campaigns.header_template.default_header' => false,
        	'campaigns.header_template.fancy_header' => true,
	] );
}

``` 