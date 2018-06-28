<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\Fixtures\FakeChoiceFactory;

use WMDE\Fundraising\Frontend\BucketTesting\FeatureToggle;
use WMDE\Fundraising\Frontend\Factories\ChoiceFactory;
use WMDE\Fundraising\Frontend\Factories\UnknownChoiceDefinition;
use WMDE\Fundraising\Frontend\Tests\Unit\Cli\CampaignUtilizationValidatorTest;

/**
 * @see CampaignUtilizationValidatorTest This class is used to test if bucket testing logic behaves as expected
 * @see ChoiceFactory The actual class used by the application
 *
 * @license GNU GPL v2+
 */
class FakeInconsistentChoiceFactory {

	private $featureToggle;

	public function __construct( FeatureToggle $featureToggle ) {
		$this->featureToggle = $featureToggle;
	}

	public function someFakeBucketSelection(): string {
		if ( $this->featureToggle->featureIsActive( 'campaigns.test_campaign.test_bucket_a' ) ) {
			return 'Some_String_Value';
		} elseif ( $this->featureToggle->featureIsActive( 'campaigns.test_campaign.test_bucket_b' ) ) {
			return 'Another_String_Value';
		}
		throw new UnknownChoiceDefinition( 'This code should never be reached.' );
	}

	public function anotherFakeBucketSelection(): string {
		if ( $this->featureToggle->featureIsActive( 'campaigns.another_test_campaign.test_bucket_c' ) ) {
			return 'Some_String_Value';
		} elseif ( $this->featureToggle->featureIsActive( 'campaigns.another_test_campaign.test_bucket_d' ) ) {
			return 'Another_String_Value';
		} elseif ( $this->featureToggle->featureIsActive( 'campaigns.another_test_campaign.test_bucket_e' ) ) {
			return 'Yet_Another_String_Value';
		}
		throw new UnknownChoiceDefinition( 'This code should never be reached.' );
	}

	public function incompleteCopyOfanotherFakeBucketSelection(): int {
		if ( $this->featureToggle->featureIsActive( 'campaigns.another_test_campaign.test_bucket_c' ) ) {
			return 123;
		} elseif ( $this->featureToggle->featureIsActive( 'campaigns.another_test_campaign.test_bucket_d' ) ) {
			return 456;
		}
		throw new UnknownChoiceDefinition( 'This code should never be reached.' );
	}
}
