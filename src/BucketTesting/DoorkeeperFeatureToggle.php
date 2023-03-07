<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\BucketTesting;

use RemotelyLiving\Doorkeeper\Doorkeeper;

class DoorkeeperFeatureToggle implements FeatureToggle {

	public function __construct( private readonly Doorkeeper $doorkeeper ) {
	}

	public function featureIsActive( string $featureId ): bool {
		return $this->doorkeeper->grantsAccessTo( $featureId );
	}

}
