<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\BucketTesting\Logging;

use WMDE\Fundraising\Frontend\BucketTesting\Bucket;

/**
 * Logs the event info, the bucket data and the time as a JSON-encoded string
 */
class JsonBucketLogger implements BucketLogger {

	private $logWriter;
	private $timeTeller;

	public function __construct( LogWriter $logWriter, TimeTeller $timeTeller ) {
		$this->logWriter = $logWriter;
		$this->timeTeller = $timeTeller;
	}

	public function writeEvent( LoggingEvent $event, Bucket ...$buckets ): void {
		$this->logWriter->write( json_encode( $this->formatData( $event, ...$buckets ) ) );
	}

	private function formatData( LoggingEvent $event, Bucket ...$buckets ): array {
		return [
			'date' => $this->timeTeller->getTime(),
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
