<?php

namespace WMDE\Fundraising\Frontend\Tests\Unit\BucketTesting;

use Doctrine\ORM\EntityManager;
use Doctrine\Persistence\ObjectRepository;
use PHPUnit\Framework\TestCase;
use WMDE\Fundraising\Frontend\BucketTesting\Bucket;
use WMDE\Fundraising\Frontend\BucketTesting\Campaign;
use WMDE\Fundraising\Frontend\BucketTesting\CampaignDate;
use WMDE\Fundraising\Frontend\BucketTesting\DataAccess\DoctrineBucketLogRepository;
use WMDE\Fundraising\Frontend\BucketTesting\Domain\Model\BucketLog;
use WMDE\Fundraising\Frontend\BucketTesting\Logging\DatabaseBucketLogger;
use WMDE\Fundraising\Frontend\BucketTesting\Logging\LoggingError;
use WMDE\Fundraising\Frontend\Tests\Fixtures\FakeBucketLoggingEvent;
use WMDE\Fundraising\Frontend\Tests\TestEnvironment;

/**
 * @covers \WMDE\Fundraising\Frontend\BucketTesting\Logging\DatabaseBucketLogger
 */
class DatabaseBucketLoggerTest extends TestCase {

	private EntityManager $entityManager;

	public function setUp(): void {
		$this->entityManager = TestEnvironment::newInstance()->getFactory()->getEntityManager();
		parent::setUp();
	}

	private function getDatabaseBucketLogger(): DatabaseBucketLogger {
		return new DatabaseBucketLogger( new DoctrineBucketLogRepository( $this->entityManager ) );
	}

	private function getOrmRepository(): ObjectRepository {
		return $this->entityManager->getRepository( BucketLog::class );
	}

	private function newBucket( string $bucketName, string $campaignName ): Bucket {
		return new Bucket(
			$bucketName,
			new Campaign(
				$campaignName,
				$bucketName . $campaignName,
				new CampaignDate(),
				( new CampaignDate() )->modify( '+1 month' ),
				true
			),
			true
		);
	}

	public function testWhenBucketLogIsCreatedAddsBucketLog() {
		$databaseBucketLogger = $this->getDatabaseBucketLogger();
		$event = new FakeBucketLoggingEvent();

		$databaseBucketLogger->writeEvent( $event );

		/** @var BucketLog[] $bucketLogs */
		$bucketLogs = $this->getOrmRepository()->findAll();

		$this->assertCount( 1, $bucketLogs );
		$this->assertEquals( $event->getMetaData()['id'], $bucketLogs[0]->getExternalId() );
		$this->assertEquals( $event->getName(), $bucketLogs[0]->getEventName() );
	}

	public function testWhenBucketLogIsCreatedAddsRelatedBuckets() {
		$databaseBucketLogger = $this->getDatabaseBucketLogger();
		$bucket1 = $this->newBucket( 'bucket_1', 'campaign_1' );
		$bucket2 = $this->newBucket( 'bucket_2', 'campaign_1' );

		$databaseBucketLogger->writeEvent( new FakeBucketLoggingEvent(), $bucket1, $bucket2 );

		/** @var BucketLog[] $bucketLogs */
		$bucketLogs = $this->getOrmRepository()->findAll();

		$this->assertCount( 2, $bucketLogs[0]->getBuckets() );
		$this->assertEquals( $bucket1->getName(), $bucketLogs[0]->getBuckets()[0]->getName() );
		$this->assertEquals( $bucket1->getCampaign()->getName(), $bucketLogs[0]->getBuckets()[0]->getCampaign() );
		$this->assertEquals( $bucket2->getName(), $bucketLogs[0]->getBuckets()[1]->getName() );
		$this->assertEquals( $bucket2->getCampaign()->getName(), $bucketLogs[0]->getBuckets()[1]->getCampaign() );
	}

	public function testWhenNotPassedEntityIdThrowsException() {
		$databaseBucketLogger = $this->getDatabaseBucketLogger();

		$this->expectException( LoggingError::class );

		$databaseBucketLogger->writeEvent( new FakeBucketLoggingEvent( [] ) );
	}
}
