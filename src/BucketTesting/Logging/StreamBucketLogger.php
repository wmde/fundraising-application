<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\BucketTesting\Logging;

use DateTime;
use WMDE\Fundraising\Frontend\BucketTesting\Bucket;

class StreamBucketLogger implements BucketLogger {

	private $stream;
	private $dateFormat = DateTime::RFC3339_EXTENDED;

	/**
	 * @param resource $stream
	 */
	public function __construct( $stream ) {
		$this->stream = $stream;
	}

	public function writeEvent( LoggingEvent $event, Bucket ...$buckets ) {
		fwrite(
			$this->stream,
			json_encode( [
				'date' => date( $this->dateFormat ),
				'eventName' => $event->getName(),
				'metadata' => $event->getMetaData(),
				'buckets' => array_reduce(
					$buckets,
					function ( array $bucketMap, Bucket $bucket ) {
						$bucketMap[$bucket->getCampaign()->getName()] = $bucket->getName();
						return $bucketMap;
					},
					[]
				)
			] ) . "\n"
		);
	}

	public function setDateFormat( string $dateFormat ): void {
		$this->dateFormat = $dateFormat;
	}

}
