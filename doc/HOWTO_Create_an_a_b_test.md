# How to create an A/B test for a feature

This document describes how you create an A/B test for a feature. If the feature affects the behavior of the PHP code or the client-side code in the skins `10h16` or `cat17`, follow sections 1-4. If the test affects the client-side code written with [Vue.js](https://vuejs.org/), follow the sections 1 and 5.

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
* Throw `UnknownChoiceDefinition` with a proper error message after checking all buckets. This is an additional safeguard 
against changed or misspelled campaign and bucket names. It is a case of "this should never happen" if the campaign 
file is validated by the CI.

If you want to have optimum performance, check the default bucket *first*, then check all the other buckets.

### Example implementation - parameterized instance

```php
public function getHeaderTemplate( Twig_Environment $twig ): TwigTemplate {
	if ( $this->featureToggle->isFeatureActive( 'campaigns.header_template.default_header' ) {
		return new TwigTemplate( $twig, 'Header.html.twig' );
	} elseif ( $this->featureToggle->isFeatureActive( 'campaigns.header_template.fancy_header' ) {
		return new TwigTemplate( $twig, 'Fancy_Header.html.twig' );
	}
	throw new UnknownChoiceDefinition( 'Failed to determine header template' );
}
```

### Example implementation - different implementations

```php
public function getHeaderTemplate( Twig_Environment $twig ): HeaderTemplateInterface {
	if ( $this->featureToggle->isFeatureActive( 'campaigns.header_template.default_header' ) {
		return new HeaderTemplate( $twig );
	} elseif ( $this->featureToggle->isFeatureActive( 'campaigns.header_template.fancy_header' ) {
		return new FancyHeaderTemplate( $twig );
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
Please note that additional buckets you provide will be *merged* with the existing buckets. If you need to change the 
order of the buckets or remove buckets, use the callback parameter of `OverridingCampaignConfigurationLoader`.

Deactivating the campaigns ensures that you always test the default path. 
If you want to test other buckets in the campaign, you need to explicitly set a different default bucket by overriding 
the campaign loader with an `OverridingCampaignConfigurationLoader` like this:

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

## 5. Implement A/B testing for Vue.js client-side code

On the client side you have two options to influence the behavior and design of the app: For design changes, you add the bucket classes and write CSS for them. For behavior changes or additional components you you the FeatureToggle plugin. You can mix and match between the approaches, depending on your need.

The PHP code provides the IDs of the selected buckets as template variables to the client-side code. The `pageData` object you use in your entry point JavaScript files has the `selectedBuckets` property.

### Doing CSS-based changes

To visually differentiate between different buckets, you can  convert the bucket IDs to CSS class names with the `bucketIdToCssClass` function and add them to the `App` component as a property:

```typescript
import Vue from 'vue';
import PageDataInitializer from '@/page_data_initializer';
import { bucketIdToCssClass } from '@bucket_id_to_css_class';
import App from '@/components/App.vue';

interface MyApplicationDataModel {
    prop1: string,
    prop2: string,
    // etc
}

const pageData = new PageDataInitializer<MyApplicationDataModel>( '#app' );

new Vue( {
		render: h => h( App, {
			props: {
				assetsPath: pageData.assetsPath,
				bucketClasses: bucketIdToCssClass( pageData.selectedBuckets ),
			},
		})
} );
```

`bucketIdToCssClass` converts a bucket id in the format `campaigns.CAMPAIGN_NAME.BUCKET_NAME` to a CSS class in the format `campaigns--campaign-name--bucket-name`. You can prefix your CSS classes with the bucket CSS class name to display your content differently.

### Initialize FeatureToggle Plugin
The PHP code provides the IDs of the selected buckets as template variables to the client-side code. You can use the IDs as a configuration for initializing the FeatureToggle plugin like this:

```typescript
import Vue from 'vue';
import { FeatureTogglePlugin } from "@/FeatureToggle";
import PageDataInitializer from '@/page_data_initializer';

interface MyApplicationDataModel {
    prop1: string,
    prop2: string,
    // etc
}

const pageData = new PageDataInitializer<MyApplicationDataModel>( '#app' );

Vue.use( FeatureTogglePlugin, { activeFeatures: pageData.selectedBuckets } );

```

With this initialization code, the new component `<feature-toggle>` becomes available in all of your components.

You don't need to add the initialization code to *every* entry point, only to those entry points which use components that contain `<feature-toggle>` components.

### Use `<feature-toggle>` component
If you want a component to show up conditionally, wrap it in a `<feature-toggle>` and add a slot attribute to it that follows the pattern `campaigns.<CAMPAIGN_NAME>.<GROUP_NAME>`. The `<feature-toggle>` component will hide all children where the `slot` attribute does not match an active and selected bucket. It will also hide all children without a `slot` attribute.

In the following example, only one headline will be shown, depending on the server-side selection of the bucket for the `header_template` campaign.
````vue
<template>
    <div>
        <feature-toggle>
            <h1 slot="campaigns.header_template.default_header">Default Header</h1>
            <h1 slot="campaigns.header_template.fancy_header" class="fancy">Fancy Header</h1>
        </feature-toggle>
    </div>
</template>
````  

Although it's good style, you don't need to list all possible bucket options for a campaign  inside a `<feature-toggle>` component. You can even list bucket options for *multiple* campaigns inside a `<feature-toggle>` component.

### Unit-test components with `<feature-toggle>` children 
To pass the unit tests for components that contain the `<feature-toggle>` component, you need to use the [`localVue` option](https://vue-test-utils.vuejs.org/api/options.html#localvue) when mounting the component:

```typescript
import { createLocalVue, mount } from '@vue/test-utils';
import { FeatureTogglePlugin } from "@/FeatureToggle";
import MyComponent from "@/components/MyComponent";

const localVue = createLocalVue();
localVue.use( FeatureTogglePlugin, { 
    activeFeatures: [ 'campaigns.header_template.default' ] 
} );

const wrapper = mount(MyComponent, { localVue });
expect(wrapper.vm.find('.fancy')).toBeEmpty();
```

Make sure to cover all the code paths by initializing different `localVue` instances with different settings for `activeFeatures`!

If you get the error message `Unknown custom element: <feature-toggle> - did you register the component correctly?` during testing, that's an indicator that you have to use `createLocalVue` and use the `FeatureTogglePlugin` in your test.