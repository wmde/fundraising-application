<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\BucketTesting;

use DateTime;

class StreamBucketLogWriter implements BucketLogWriter {

	private $stream;
	private $dateFormat = DateTime::RFC3339_EXTENDED;

	/**
	 * @param resource $stream
	 */
	public function __construct( $stream ) {
		$this->stream = $stream;
	}

	public function writeEvent( string $eventName, array $eventMetadata, Bucket ...$buckets ) {
		fwrite( $this->stream, json_encode( [
			'date' => date( $this->dateFormat ),
			'eventName' => $eventName,
			'metadata' => $eventMetadata,
			'buckets' => array_reduce( $buckets, function ( array $bucketMap, Bucket $bucket ) {
				$bucketMap[$bucket->getCampaign()->getName()] = $bucket->getName();
				return $bucketMap;
			}, [] )
		]));
	}

	public function setDateFormat( string $dateFormat ): void {
		$this->dateFormat = $dateFormat;
	}

}