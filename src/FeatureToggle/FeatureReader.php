<?php
declare( strict_types=1 );

namespace WMDE\Fundraising\Frontend\FeatureToggle;

interface FeatureReader {
	/**
	 * @return Feature[]
	 */
	public function getFeatures(): array;
}
