<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\Fixtures;

use WMDE\Fundraising\Frontend\BucketTesting\Domain\Model\Bucket;
use WMDE\Fundraising\Frontend\BucketTesting\Logging\BucketLogger;
use WMDE\Fundraising\Frontend\BucketTesting\Logging\LoggingEvent;

class BucketLoggerSpy implements BucketLogger {

	/**
	 * @var array<int, array{event: LoggingEvent, buckets: Bucket[]}>
	 */
	private array $events = [];

	public function writeEvent( LoggingEvent $event, Bucket ...$buckets ): void {
		$this->events[] = [
			'event' => $event,
			'buckets' => $buckets
		];
	}

	public function getEventCount(): int {
		return count( $this->events );
	}

	public function getFirstEvent(): LoggingEvent {
		return $this->events[0]['event'];
	}

	/**
	 * @return Bucket[]
	 */
	public function getFirstBuckets(): array {
		return $this->events[0]['buckets'];
	}
}
