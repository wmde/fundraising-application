<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Factories;

use WMDE\Fundraising\Frontend\BucketTesting\FeatureToggle;

/**
 * Factory for generating classes whose implementations differ due to A/B testing.
 */
class ChoiceFactory {

	/** @phpstan-ignore-next-line property.onlyWritten $featureToggle is currently never read. Remove this ignore statement when a new AB test setup uses the property */
	public function __construct( private readonly FeatureToggle $featureToggle ) {
	}

}
