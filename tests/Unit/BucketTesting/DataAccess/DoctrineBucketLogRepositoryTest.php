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
		$bucketLog->getBuckets()->add( new BucketLogBucket( 'test_bucket', 'test_campaign' ) );

		$doctrineBucketLogRepository->storeBucketLog( $bucketLog );

		/** @var BucketLog[] $bucketLogs */
		$bucketLogs = $this->getOrmRepository()->findAll();

		$this->assertCount( 1, $bucketLogs );
		$this->assertEquals( 99999, $bucketLogs[0]->getExternalId() );
		$this->assertEquals( 'test_event', $bucketLogs[0]->getEventName() );
		$this->assertCount( 1, $bucketLogs[0]->getBuckets() );
		$this->assertEquals( 'test_bucket', $bucketLogs[0]->getBuckets()[0]->getName() );
		$this->assertEquals( 'test_campaign', $bucketLogs[0]->getBuckets()[0]->getCampaign() );
	}
}
