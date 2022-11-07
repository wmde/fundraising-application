<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Factories;

use WMDE\Fundraising\Frontend\BucketTesting\FeatureToggle;

/**
 * Factory for generating classes whose implementations differ due to A/B testing.
 *
 * @license GPL-2.0-or-later
 */
class ChoiceFactory {

	/**
	 * @var FeatureToggle
	 *
	 * To save us having to delete and recreate this class, this is ignored
	 * until the next time we need to A/B test a feature in the application.
	 * @phpstan-ignore-next-line
	 */
	private FeatureToggle $featureToggle;

	public function __construct( FeatureToggle $featureToggle ) {
		$this->featureToggle = $featureToggle;
	}
}
