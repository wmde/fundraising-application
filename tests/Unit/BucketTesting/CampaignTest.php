<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\Unit\BucketTesting;

use PHPUnit\Framework\TestCase;
use WMDE\Fundraising\Frontend\BucketTesting\Campaign;
use WMDE\Fundraising\Frontend\BucketTesting\Bucket;
use WMDE\Fundraising\Frontend\BucketTesting\CampaignDate;

class CampaignTest extends TestCase {

	public function testBucketsAddedGetAnIndexInTheOrderTheyWereAdded() {
		$campaign = new Campaign( 'test', 't', new CampaignDate(), new CampaignDate(), true );
		$firstBucket = new Bucket( 'default', $campaign, Bucket::DEFAULT );
		$secondBucket = new Bucket( 'variant_1', $campaign, Bucket::NON_DEFAULT );
		$thirdBucket = new Bucket( 'variant_2', $campaign, Bucket::NON_DEFAULT );

		$campaign->addBucket( $firstBucket )->addBucket( $secondBucket )->addBucket( $thirdBucket );

		$this->assertSame( $firstBucket, $campaign->getBucketByIndex( 0 ) );
		$this->assertSame( $secondBucket, $campaign->getBucketByIndex( 1 ) );
		$this->assertSame( $thirdBucket, $campaign->getBucketByIndex( 2 ) );
	}

	public function testCampaignsCanReturnIndexesForBuckets() {
		$campaign = new Campaign( 'test', 't', new CampaignDate(), new CampaignDate(), true );
		$firstBucket = new Bucket( 'default', $campaign, Bucket::DEFAULT );
		$secondBucket = new Bucket( 'variant_1', $campaign, Bucket::NON_DEFAULT );
		$thirdBucket = new Bucket( 'variant_2', $campaign, Bucket::NON_DEFAULT );

		$campaign->addBucket( $firstBucket )->addBucket( $secondBucket )->addBucket( $thirdBucket );

		$this->assertSame( 0, $campaign->getIndexByBucket( $firstBucket ) );
		$this->assertSame( 1, $campaign->getIndexByBucket( $secondBucket ) );
		$this->assertSame( 2, $campaign->getIndexByBucket( $thirdBucket ) );
	}

	public function testGivenABucketThatIsNotAddedToCampaigns_campaignWillThrowAnException() {
		$campaign = new Campaign( 'test', 't', new CampaignDate(), new CampaignDate(), true );
		$firstBucket = new Bucket( 'default', $campaign, Bucket::DEFAULT );
		$secondBucket = new Bucket( 'variant_1', $campaign, Bucket::NON_DEFAULT );
		$thirdBucket = new Bucket( 'variant_2', $campaign, Bucket::NON_DEFAULT );

		$campaign->addBucket( $firstBucket )->addBucket( $secondBucket );

		$this->expectException( \OutOfBoundsException::class );
		$campaign->getIndexByBucket( $thirdBucket );
	}

	public function testGivenAnUnknownIndex_campaignWillReturnNull() {
		$campaign = new Campaign( 'test', 't', new CampaignDate(), new CampaignDate(), true );

		$this->assertNull( $campaign->getBucketByIndex( 0 ) );
	}

	public function testGivenDefaultBucket_campaignCanReturnIt() {
		$campaign = new Campaign( 'test', 't', new CampaignDate(), new CampaignDate(), true );
		$firstBucket = new Bucket( 'default', $campaign, Bucket::DEFAULT );
		$secondBucket = new Bucket( 'variant_1', $campaign, Bucket::NON_DEFAULT );

		$campaign->addBucket( $firstBucket )->addBucket( $secondBucket );

		$this->assertSame( $firstBucket, $campaign->getDefaultBucket() );
	}

	public function testGivenCampaignWithNoBuckets_getDefaultBucketWillThrowException() {
		$campaign = new Campaign( 'test', 't', new CampaignDate(), new CampaignDate(), true );

		$this->expectException( \LogicException::class );
		$campaign->getDefaultBucket();
	}

	public function testGivenCampaignWithNoDefaultBuckets_getDefaultBucketWillThrowException() {
		$campaign = new Campaign( 'test', 't', new CampaignDate(), new CampaignDate(), true );
		$firstBucket = new Bucket( 'default', $campaign, Bucket::NON_DEFAULT );
		$secondBucket = new Bucket( 'variant_1', $campaign, Bucket::NON_DEFAULT );

		$campaign->addBucket( $firstBucket )->addBucket( $secondBucket );

		$this->expectException( \LogicException::class );
		$campaign->getDefaultBucket();
	}

	public function testGivenCampaignwithDateRange_itCanBeCheckedForExpiration() {
		$campaign = new Campaign( 'test', 't', new CampaignDate( '2018-10-01' ), new CampaignDate( '2018-12-31' ), true );

		$this->assertTrue( $campaign->isExpired( new CampaignDate( '2018-09-09' ) ), 'Campaign is expired before start date' );
		$this->assertTrue( $campaign->isExpired( new CampaignDate( '2025-02-25' ) ), 'Campaign is expired after end date' );
		$this->assertFalse( $campaign->isExpired( new CampaignDate( '2018-10-02' ) ), 'Campaign is not expired inside date range' );
	}
}
