<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\Fixtures;

use WMDE\Fundraising\Frontend\BucketTesting\Logging\LoggingEvent;

class FakeBucketLoggingEvent implements LoggingEvent {

	public function getMetaData(): array {
		return [ 'id' => 123, 'some_fact' => 'water_is_wet' ];
	}

	public function getName(): string {
		return 'testEventLogged';
	}

}