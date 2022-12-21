<?php
declare( strict_types=1 );

namespace WMDE\Fundraising\Frontend\Tests\Fixtures;

use WMDE\Fundraising\Frontend\FeatureToggle\FeatureReader;

class FeatureReaderStub implements FeatureReader {
	public function getFeatures(): array {
		return [];
	}

}
