<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\Fixtures;

use WMDE\Fundraising\Frontend\BucketTesting\FeatureToggle;
use WMDE\Fundraising\Frontend\Factories\UnknownChoiceDefinition;

/**
 * @license GNU GPL v2+
 */
class ChoiceFactoryMock {

	private $featureToggle;

	public function __construct( FeatureToggle $featureToggle ) {
		$this->featureToggle = $featureToggle;
	}

	public function someMockBucketSelection(): void {
		if ( $this->featureToggle->featureIsActive( 'campaigns.test_campaign.test_bucket_a' ) ) {
			echo 'I am doing something';
		} elseif ( $this->featureToggle->featureIsActive( 'campaigns.test_campaign.test_bucket_b' ) ) {
			echo 'I am doing something else';
		}
		throw new UnknownChoiceDefinition( 'This code should never be reached.' );
	}

	public function anotherMockBucketSelection(): void {
		if ( $this->featureToggle->featureIsActive( 'campaigns.another_test_campaign.test_bucket_c' ) ) {
			echo 'I am doing something';
		} elseif ( $this->featureToggle->featureIsActive( 'campaigns.another_test_campaign.test_bucket_d' ) ) {
			echo 'I am doing something else';
		} elseif ( $this->featureToggle->featureIsActive( 'campaigns.another_test_campaign.test_bucket_e' ) ) {
			echo 'I am also doing something else';
		}
		throw new UnknownChoiceDefinition( 'This code should never be reached.' );
	}
}
