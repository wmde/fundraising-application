<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\Unit\BucketTesting\DataAccess;

use Doctrine\ORM\EntityManager;
use Doctrine\Persistence\ObjectRepository;
use PHPUnit\Framework\TestCase;
use WMDE\Fundraising\Frontend\BucketTesting\DataAccess\DoctrineBucketLogRepository;
use WMDE\Fundraising\Frontend\BucketTesting\Domain\Model\BucketLog;
use WMDE\Fundraising\Frontend\BucketTesting\Domain\Model\BucketLogBucket;
use WMDE\Fundraising\Frontend\Tests\TestEnvironment;

/**
 * @covers \WMDE\Fundraising\Frontend\BucketTesting\DataAccess\DoctrineBucketLogRepository
 */
class DoctrineBucketLogRepositoryTest extends TestCase {

	private EntityManager $entityManager;

	public function setUp(): void {
		$this->entityManager = TestEnvironment::newInstance()->getFactory()->getEntityManager();
		parent::setUp();
	}

	private function getOrmRepository(): ObjectRepository {
		return $this->entityManager->getRepository( BucketLog::class );
	}

	public function testWhenGivenBucketLogItIsStored() {
		$doctrineBucketLogRepository = new DoctrineBucketLogRepository( $this->entityManager );

		$bucketLog = new BucketLog( 99999, 'test_event' );
		$bucketLog->addBucket( 'test_bucket', 'test_campaign' );

		$doctrineBucketLogRepository->storeBucketLog( $bucketLog );

		/** @var BucketLog[] $bucketLogs */
		$bucketLogs = $this->getOrmRepository()->findAll();
		$bucketLog = $bucketLogs[0];
		$bucketLogBucket = $bucketLog->getBuckets()[0];

		$this->assertCount( 1, $bucketLogs );
		$this->assertEquals( 99999, $bucketLog->getExternalId() );
		$this->assertEquals( 'test_event', $bucketLog->getEventName() );
		$this->assertCount( 1, $bucketLog->getBuckets() );
		$this->assertEquals( 'test_bucket', $bucketLogBucket->getName() );
		$this->assertEquals( 'test_campaign', $bucketLogBucket->getCampaign() );
		$this->assertEquals( $bucketLog, $bucketLogBucket->getBucketLog() );
	}
}
