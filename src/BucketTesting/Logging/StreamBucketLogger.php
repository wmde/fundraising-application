<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\BucketTesting\Logging;

use DateTime;
use WMDE\Fundraising\Frontend\BucketTesting\Bucket;

class StreamBucketLogger implements BucketLogger {

	private $stream;
	private $timeTeller;

	/**
	 * @param resource $stream
	 * @param TimeTeller $timeTeller
	 */
	public function __construct( $stream, TimeTeller $timeTeller ) {
		$this->stream = $stream;
		$this->timeTeller = $timeTeller;
	}

	public function writeEvent( LoggingEvent $event, Bucket ...$buckets ) {
		fwrite(
			$this->stream,
			json_encode( [
				'date' => $this->timeTeller->getTime(),
				'eventName' => $event->getName(),
				'metadata' => $event->getMetaData(),
				'buckets' => $this->getBucketMap( $buckets )
			] ) . "\n"
		);
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
