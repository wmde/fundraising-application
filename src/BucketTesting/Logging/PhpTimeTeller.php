<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\BucketTesting\Logging;

use DateTime;

class PhpTimeTeller implements TimeTeller {

	private $dateFormat;

	public function __construct( string $dateFormat = DateTime::RFC3339_EXTENDED ) {
		$this->dateFormat = $dateFormat;
	}

	public function getTime(): string {
		return date( $this->dateFormat );
	}

}
