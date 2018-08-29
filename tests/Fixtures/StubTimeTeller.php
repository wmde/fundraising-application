<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\Fixtures;

use WMDE\Fundraising\Frontend\BucketTesting\Logging\TimeTeller;

class StubTimeTeller implements TimeTeller  {

	private $stubValue;

	public function __construct( string $stubValue ) {
		$this->stubValue = $stubValue;
	}

	public function getTime(): string {
		return $this->stubValue;
	}

}
