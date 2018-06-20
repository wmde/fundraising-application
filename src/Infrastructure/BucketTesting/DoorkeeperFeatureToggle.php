<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Infrastructure\BucketTesting;

use RemotelyLiving\Doorkeeper\Doorkeeper;

class DoorkeeperFeatureToggle implements FeatureToggle {

	private $doorkeeper;

	public function __construct( Doorkeeper $doorkeeper ) {
		$this->doorkeeper = $doorkeeper;
	}

	public function featureIsActive( string $featureId ): bool {
		return $this->doorkeeper->grantsAccessTo( $featureId );
	}

}