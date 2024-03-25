<?php
declare( strict_types=1 );

namespace WMDE\Fundraising\Frontend\Tests\Fixtures;

use WMDE\Fundraising\Frontend\FeatureToggle\Feature;
use WMDE\Fundraising\Frontend\FeatureToggle\FeatureReader;

class FeatureReaderStub implements FeatureReader {

	/**
	 * @return Feature[]
	 */
	public function getFeatures(): array {
		return [];
	}

}
