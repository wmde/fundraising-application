<?php

namespace WMDE\Fundraising\Frontend\Tests\Unit\BucketTesting;

use Doctrine\ORM\EntityManager;
use Doctrine\Persistence\ObjectRepository;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use WMDE\Fundraising\Frontend\BucketTesting\DataAccess\DoctrineBucketLogRepository;
use WMDE\Fundraising\Frontend\BucketTesting\Domain\Model\Bucket;
use WMDE\Fundraising\Frontend\BucketTesting\Domain\Model\BucketLog;
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
		$factory = static::$container->get( FunFunFactory::class );
		static::rebuildDatabaseSchema( $factory );
		$this->entityManager = $factory->getEntityManager();
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
		$bucketLog = $bucketLogs[0];
		$bucketLogBucket1 = $bucketLog->getBuckets()[0];
		$bucketLogBucket2 = $bucketLog->getBuckets()[1];

		$this->assertCount( 2, $bucketLog->getBuckets() );
		$this->assertEquals( $bucket1->getName(), $bucketLogBucket1->getName() );
		$this->assertEquals( $bucket1->getCampaign()->getName(), $bucketLogBucket1->getCampaign() );
		$this->assertEquals( $bucketLog, $bucketLogBucket1->getBucketLog() );
		$this->assertEquals( $bucket2->getName(), $bucketLogBucket2->getName() );
		$this->assertEquals( $bucket2->getCampaign()->getName(), $bucketLogBucket2->getCampaign() );
		$this->assertEquals( $bucketLog, $bucketLogBucket2->getBucketLog() );
	}

	public function testWhenNotPassedEntityIdThrowsException() {
		$databaseBucketLogger = $this->getDatabaseBucketLogger();

		$this->expectException( LoggingError::class );

		$databaseBucketLogger->writeEvent( new FakeBucketLoggingEvent( [] ) );
	}
}
