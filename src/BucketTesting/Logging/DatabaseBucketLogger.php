<?php

namespace WMDE\Fundraising\Frontend\BucketTesting\Logging;

use WMDE\Fundraising\Frontend\BucketTesting\Domain\BucketLoggingRepository;
use WMDE\Fundraising\Frontend\BucketTesting\Domain\Model\Bucket;
use WMDE\Fundraising\Frontend\BucketTesting\Domain\Model\BucketLog;
use WMDE\Fundraising\Frontend\BucketTesting\Domain\Model\BucketLogBucket;
use WMDE\Fundraising\Frontend\BucketTesting\Domain\Model\CampaignDate;

class DatabaseBucketLogger implements BucketLogger {

	private BucketLoggingRepository $bucketLoggingRepository;

	public function __construct( BucketLoggingRepository $bucketLoggingRepository ) {
		$this->bucketLoggingRepository = $bucketLoggingRepository;
	}

	public function writeEvent( LoggingEvent $event, Bucket ...$buckets ): void {
		$metadata = $event->getMetaData();

		if ( !isset( $metadata['id'] ) ) {
			throw new LoggingError( 'Database Bucket Logger expects an external ID to be present' );
		}

		$bucketLog = new BucketLog( $metadata['id'], $event->getName() );
		$this->addBucketLogBuckets( $bucketLog, $buckets );

		$this->bucketLoggingRepository->storeBucketLog( $bucketLog );
	}

	private function addBucketLogBuckets( BucketLog $bucketLog, array $buckets ): void {
		foreach ( $buckets as $bucket ) {
			if ( !$bucket->getCampaign()->isActive() || $bucket->getCampaign()->isExpired( new CampaignDate() ) ) {
				continue;
			}
			$bucketLog->addBucket(
				$bucket->getName(),
				$bucket->getCampaign()->getName()
			);
		}
	}
}
