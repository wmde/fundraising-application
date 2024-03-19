<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\BucketTesting\DataAccess;

use Doctrine\ORM\EntityManager;
use WMDE\Fundraising\Frontend\BucketTesting\Domain\BucketLoggingRepository;
use WMDE\Fundraising\Frontend\BucketTesting\Domain\Model\BucketLog;

class DoctrineBucketLogRepository implements BucketLoggingRepository {

	public function __construct( private readonly EntityManager $entityManager ) {
	}

	public function storeBucketLog( BucketLog $bucketLog ): void {
		$this->entityManager->persist( $bucketLog );
		$this->entityManager->flush();
	}
}
