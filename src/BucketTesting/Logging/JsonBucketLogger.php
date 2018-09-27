<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\BucketTesting\Logging;

use DateTime;
use WMDE\Clock\Clock;
use WMDE\Fundraising\Frontend\BucketTesting\Bucket;

/**
 * Logs the event info, the bucket data and the time as a JSON-encoded string
 */
class JsonBucketLogger implements BucketLogger {

	private $logWriter;
	private $clock;

	public function __construct( LogWriter $logWriter, Clock $clock ) {
		$this->logWriter = $logWriter;
		$this->clock = $clock;
	}

	public function writeEvent( LoggingEvent $event, Bucket ...$buckets ): void {
		$this->logWriter->write( json_encode( $this->formatData( $event, ...$buckets ) ) );
	}

	private function formatData( LoggingEvent $event, Bucket ...$buckets ): array {
		return [
			'date' => $this->clock->now()->format( DateTime::RFC3339_EXTENDED ),
			'eventName' => $event->getName(),
			'metadata' => $event->getMetaData(),
			'buckets' => $this->getBucketMap( $buckets )
		];
	}

	private function getBucketMap( array $buckets ): array {
		return array_reduce(
			$buckets,
			function ( array $bucketMap, Bucket $bucket ) {
				$bucketMap[$bucket->getCampaign()->getName()] = $bucket->getName();
				return $bucketMap;
			},
			[]
		);
	}

}
