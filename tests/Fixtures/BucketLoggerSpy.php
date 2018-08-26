<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\Fixtures;

use WMDE\Fundraising\Frontend\BucketTesting\Bucket;
use WMDE\Fundraising\Frontend\BucketTesting\Logging\BucketLogger;

class BucketLoggerSpy implements BucketLogger {
	private $events = [];

	public function writeEvent( string $eventName, array $eventMetadata, Bucket ...$buckets ) {
		$this->events[] = [
			'eventName' => $eventName,
			'metadata' => $eventMetadata,
			'buckets' => $buckets
		];
	}

	public function getEventCount(): int {
		return count( $this->events );
	}
}
