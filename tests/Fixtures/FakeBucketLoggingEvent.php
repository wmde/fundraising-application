<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\Fixtures;

use WMDE\Fundraising\Frontend\BucketTesting\Logging\LoggingEvent;

class FakeBucketLoggingEvent implements LoggingEvent {

	public function __construct( private readonly array $metadata = [ 'id' => 123, 'some_fact' => 'water_is_wet' ] ) {
	}

	public function getMetaData(): array {
		return $this->metadata;
	}

	public function getName(): string {
		return 'testEventLogged';
	}

}
