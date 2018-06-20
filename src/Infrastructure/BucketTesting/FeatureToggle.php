<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Infrastructure\BucketTesting;

interface FeatureToggle {
	public function featureIsActive( string $featureId ): bool;
}