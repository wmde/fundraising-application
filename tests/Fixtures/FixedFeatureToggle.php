<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\Fixtures;

use WMDE\Fundraising\Frontend\Infrastructure\FeatureToggle;

class FixedFeatureToggle implements FeatureToggle {

	private $allowedFeatures;

	public function __construct( array $allowedFeatures ) {
		$this->allowedFeatures = $allowedFeatures;
	}

	public function featureIsActive( string $featureId ): bool {
		if ( !isset( $this->allowedFeatures[$featureId] ) ) {
			throw new \OutOfBoundsException( 'Unexpected feature toggle call:' . $featureId );
		}
		return $this->allowedFeatures[$featureId];
	}

}