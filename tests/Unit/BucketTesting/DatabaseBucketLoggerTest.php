<?php

namespace WMDE\Fundraising\Frontend\Tests\Unit\BucketTesting;

use Doctrine\ORM\EntityManager;
use Doctrine\Persistence\ObjectRepository;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use WMDE\Fundraising\Frontend\BucketTesting\DataAccess\DoctrineBucketLogRepository;
use WMDE\Fundraising\Frontend\BucketTesting\Domain\Model\Bucket;
use WMDE\Fundraising\Frontend\BucketTesting\Domain\Model\BucketLog;
use WMDE\Fundraising\Frontend\BucketTesting\Domain\Model\BucketLogBucket;
use WMDE\Fundraising\Frontend\BucketTesting\Domain\Model\Campaign;
use WMDE\Fundraising\Frontend\BucketTesting\Domain\Model\CampaignDate;
use WMDE\Fundraising\Frontend\BucketTesting\Logging\DatabaseBucketLogger;
use WMDE\Fundraising\Frontend\BucketTesting\Logging\LoggingError;
use WMDE\Fundraising\Frontend\Factories\FunFunFactory;
use WMDE\Fundraising\Frontend\Tests\Fixtures\FakeBucketLoggingEvent;
use WMDE\Fundraising\Frontend\Tests\RebuildDatabaseSchemaTrait;

/**
 * @covers \WMDE\Fundraising\Frontend\BucketTesting\Logging\DatabaseBucketLogger
 */
class DatabaseBucketLoggerTest extends KernelTestCase {

	use RebuildDatabaseSchemaTrait;

	private EntityManager $entityManager;

	public function setUp(): void {
		parent::setUp();
		static::bootKernel();

		/** @var FunFunFactory $factory */
		$factory = static::getContainer()->get( FunFunFactory::class );
		static::rebuildDatabaseSchema( $factory );
		$this->entityManager = $factory->getEntityManager();
	}

	private function getDatabaseBucketLogger(): DatabaseBucketLogger {
		return new DatabaseBucketLogger( new DoctrineBucketLogRepository( $this->entityManager ) );
	}

	private function getOrmRepository(): ObjectRepository {
		return $this->entityManager->getRepository( BucketLog::class );
	}

	private function newBucket(
			string $bucketName,
			string $campaignName,
			CampaignDate $start,
			CampaignDate $end,
			bool $isActive = true,
		): Bucket {
		return new Bucket(
			$bucketName,
			new Campaign(
				$campaignName,
				$bucketName . $campaignName,
				$start,
				$end,
				$isActive
			),
			true
		);
	}

	private function newActiveBucket(): Bucket {
		$start = new CampaignDate();
		$end = ( new CampaignDate() )->modify( '+1 month' );
		return $this->newBucket( 'bucket_1', 'campaign_1', $start, $end, true );
	}

	public function testWhenBucketLogIsCreatedAddsBucketLog(): void {
		$databaseBucketLogger = $this->getDatabaseBucketLogger();
		$event = new FakeBucketLoggingEvent();

		$databaseBucketLogger->writeEvent( $event, $this->newActiveBucket() );

		/** @var BucketLog[] $bucketLogs */
		$bucketLogs = $this->getOrmRepository()->findAll();

		$this->assertCount( 1, $bucketLogs );
		$this->assertEquals( $event->getMetaData()['id'], $bucketLogs[0]->getExternalId() );
		$this->assertEquals( $event->getName(), $bucketLogs[0]->getEventName() );
	}

	public function testWhenBucketLogIsCreatedAddsRelatedBuckets(): void {
		$databaseBucketLogger = $this->getDatabaseBucketLogger();
		$start = new CampaignDate();
		$end = ( new CampaignDate() )->modify( '+1 month' );
		$bucket1 = $this->newBucket( 'bucket_1', 'campaign_1', $start, $end );
		$bucket2 = $this->newBucket( 'bucket_2', 'campaign_2', $start, $end );

		$databaseBucketLogger->writeEvent( new FakeBucketLoggingEvent(), $bucket1, $bucket2 );

		/** @var BucketLog[] $bucketLogs */
		$bucketLogs = $this->getOrmRepository()->findAll();
		$bucketLog = $bucketLogs[0];

		/** @var BucketLogBucket $bucketLogBucket1 */
		$bucketLogBucket1 = $bucketLog->getBuckets()[0];
		/** @var BucketLogBucket $bucketLogBucket2 */
		$bucketLogBucket2 = $bucketLog->getBuckets()[1];

		$this->assertCount( 2, $bucketLog->getBuckets() );
		$this->assertEquals( $bucket1->getName(), $bucketLogBucket1->getName() );
		$this->assertEquals( $bucket1->getCampaign()->getName(), $bucketLogBucket1->getCampaign() );
		$this->assertEquals( $bucketLog, $bucketLogBucket1->getBucketLog() );
		$this->assertEquals( $bucket2->getName(), $bucketLogBucket2->getName() );
		$this->assertEquals( $bucket2->getCampaign()->getName(), $bucketLogBucket2->getCampaign() );
		$this->assertEquals( $bucketLog, $bucketLogBucket2->getBucketLog() );
	}

	public function testInactiveCampaignBucketDoesNotGetAdded(): void {
		$databaseBucketLogger = $this->getDatabaseBucketLogger();
		$start = new CampaignDate();
		$end = ( new CampaignDate() )->modify( '+1 month' );
		$inactiveCampaignBucket = $this->newBucket( 'bucket_1', 'campaign_1', $start, $end, false );
		$activeCampaignBucket = $this->newActiveBucket();

		$databaseBucketLogger->writeEvent( new FakeBucketLoggingEvent(), $inactiveCampaignBucket, $activeCampaignBucket );

		/** @var BucketLog[] $bucketLogs */
		$bucketLogs = $this->getOrmRepository()->findAll();
		$bucketLog = $bucketLogs[0];
		$this->assertCount( 1, $bucketLog->getBuckets() );
	}

	public function testExpiredCampaignBucketDoesNotGetAdded(): void {
		$databaseBucketLogger = $this->getDatabaseBucketLogger();
		$start = new CampaignDate();
		$end = ( new CampaignDate() )->modify( '+1 month' );
		$currentCampaignBucket = $this->newActiveBucket();
		$expiredCampaignBucket = $this->newBucket(
			'bucket_1',
			'campaign_2',
			$start->modify( '-5 month' ),
			$end->modify( '-4 month' ),
		);

		$databaseBucketLogger->writeEvent( new FakeBucketLoggingEvent(), $currentCampaignBucket, $expiredCampaignBucket );

		/** @var BucketLog[] $bucketLogs */
		$bucketLogs = $this->getOrmRepository()->findAll();
		$bucketLog = $bucketLogs[0];
		$this->assertCount( 1, $bucketLog->getBuckets() );
	}

	public function testGivenAllExpiredBucketsNothingGetsLoggedInDatabase(): void {
		$databaseBucketLogger = $this->getDatabaseBucketLogger();
		$start = new CampaignDate();
		$end = ( new CampaignDate() )->modify( '+1 month' );
		$firstExpiredBucket = $this->newBucket(
			'bucket_1',
			'campaign_1',
			$start->modify( '-5 month' ),
			$end->modify( '-4 month' ),
		);
		$secondExpiredBucket = $this->newBucket(
			'bucket_1',
			'campaign_2',
			$start->modify( '-8 month' ),
			$end->modify( '-7 month' ),
		);

		$databaseBucketLogger->writeEvent( new FakeBucketLoggingEvent(), $firstExpiredBucket, $secondExpiredBucket );

		/** @var BucketLog[] $bucketLogs */
		$bucketLogs = $this->getOrmRepository()->findAll();
		$this->assertCount( 0, $bucketLogs, 'Database should not contain a BucketLog entry' );
	}

	public function testWhenNotPassedEntityIdThrowsException(): void {
		$databaseBucketLogger = $this->getDatabaseBucketLogger();

		$this->expectException( LoggingError::class );

		$databaseBucketLogger->writeEvent( new FakeBucketLoggingEvent( [] ) );
	}
}
