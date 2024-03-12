<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\Unit\BucketTesting;

use PHPUnit\Framework\TestCase;
use WMDE\Fundraising\Frontend\BucketTesting\Domain\Model\Bucket;
use WMDE\Fundraising\Frontend\BucketTesting\Domain\Model\Campaign;
use WMDE\Fundraising\Frontend\BucketTesting\Domain\Model\CampaignDate;

/**
 * @covers \WMDE\Fundraising\Frontend\BucketTesting\Domain\Model\Campaign
 * @covers \WMDE\Fundraising\Frontend\BucketTesting\Domain\Model\Bucket
 */
class CampaignTest extends TestCase {

	public function testBucketsAddedGetAnIndexInTheOrderTheyWereAdded(): void {
		$campaign = new Campaign( 'test', 't', new CampaignDate(), new CampaignDate(), true );
		$firstBucket = new Bucket( 'default', $campaign, Bucket::DEFAULT );
		$secondBucket = new Bucket( 'variant_1', $campaign, Bucket::NON_DEFAULT );
		$thirdBucket = new Bucket( 'variant_2', $campaign, Bucket::NON_DEFAULT );

		$campaign->addBucket( $firstBucket )->addBucket( $secondBucket )->addBucket( $thirdBucket );

		$this->assertSame( $firstBucket, $campaign->getBucketByIndex( 0 ) );
		$this->assertSame( $secondBucket, $campaign->getBucketByIndex( 1 ) );
		$this->assertSame( $thirdBucket, $campaign->getBucketByIndex( 2 ) );
	}

	public function testCampaignsCanReturnIndexesForBuckets(): void {
		$campaign = new Campaign( 'test', 't', new CampaignDate(), new CampaignDate(), true );
		$firstBucket = new Bucket( 'default', $campaign, Bucket::DEFAULT );
		$secondBucket = new Bucket( 'variant_1', $campaign, Bucket::NON_DEFAULT );
		$thirdBucket = new Bucket( 'variant_2', $campaign, Bucket::NON_DEFAULT );

		$campaign->addBucket( $firstBucket )->addBucket( $secondBucket )->addBucket( $thirdBucket );

		$this->assertSame( 0, $campaign->getIndexByBucket( $firstBucket ) );
		$this->assertSame( 1, $campaign->getIndexByBucket( $secondBucket ) );
		$this->assertSame( 2, $campaign->getIndexByBucket( $thirdBucket ) );
	}

	public function testGivenABucketThatIsNotAddedToCampaigns_campaignWillThrowAnException(): void {
		$campaign = new Campaign( 'test', 't', new CampaignDate(), new CampaignDate(), true );
		$firstBucket = new Bucket( 'default', $campaign, Bucket::DEFAULT );
		$secondBucket = new Bucket( 'variant_1', $campaign, Bucket::NON_DEFAULT );
		$thirdBucket = new Bucket( 'variant_2', $campaign, Bucket::NON_DEFAULT );

		$campaign->addBucket( $firstBucket )->addBucket( $secondBucket );

		$this->expectException( \OutOfBoundsException::class );
		$campaign->getIndexByBucket( $thirdBucket );
	}

	public function testGivenAnUnknownIndex_campaignWillReturnNull(): void {
		$campaign = new Campaign( 'test', 't', new CampaignDate(), new CampaignDate(), true );

		$this->assertNull( $campaign->getBucketByIndex( 0 ) );
	}

	public function testGivenDefaultBucket_campaignCanReturnIt(): void {
		$campaign = new Campaign( 'test', 't', new CampaignDate(), new CampaignDate(), true );
		$firstBucket = new Bucket( 'default', $campaign, Bucket::DEFAULT );
		$secondBucket = new Bucket( 'variant_1', $campaign, Bucket::NON_DEFAULT );

		$campaign->addBucket( $firstBucket )->addBucket( $secondBucket );

		$this->assertSame( $firstBucket, $campaign->getDefaultBucket() );
	}

	public function testGivenCampaignWithNoBuckets_getDefaultBucketWillThrowException(): void {
		$campaign = new Campaign( 'test', 't', new CampaignDate(), new CampaignDate(), true );

		$this->expectException( \LogicException::class );
		$campaign->getDefaultBucket();
	}

	public function testGivenCampaignWithNoDefaultBuckets_getDefaultBucketWillThrowException(): void {
		$campaign = new Campaign( 'test', 't', new CampaignDate(), new CampaignDate(), true );
		$firstBucket = new Bucket( 'default', $campaign, Bucket::NON_DEFAULT );
		$secondBucket = new Bucket( 'variant_1', $campaign, Bucket::NON_DEFAULT );

		$campaign->addBucket( $firstBucket )->addBucket( $secondBucket );

		$this->expectException( \LogicException::class );
		$campaign->getDefaultBucket();
	}

	public function testGivenCampaignWithDateRange_itCanBeCheckedForExpiration(): void {
		$campaign = new Campaign( 'test', 't', new CampaignDate( '2018-10-01' ), new CampaignDate( '2018-12-31' ), true );

		$this->assertTrue( $campaign->isExpired( new CampaignDate( '2018-09-09' ) ), 'Campaign is expired before start date' );
		$this->assertTrue( $campaign->isExpired( new CampaignDate( '2025-02-25' ) ), 'Campaign is expired after end date' );
		$this->assertFalse( $campaign->isExpired( new CampaignDate( '2018-10-02' ) ), 'Campaign is not expired inside date range' );
	}

	public function testCampaignsCanBeActiveAndInactive(): void {
		$activeCampaign = new Campaign( 'test', 't', new CampaignDate( '2018-10-01' ), new CampaignDate( '2018-12-31' ), Campaign::ACTIVE );
		$inActiveCampaign = new Campaign( 'test', 't', new CampaignDate( '2018-10-01' ), new CampaignDate( '2018-12-31' ), Campaign::INACTIVE );

		$this->assertTrue( $activeCampaign->isActive() );
		$this->assertFalse( $inActiveCampaign->isActive() );
	}

	public function testCampaignsUrlParameterCanBeActiveAndInactive(): void {
		$activeCampaign = new Campaign( 'test', 't', new CampaignDate( '2018-10-01' ), new CampaignDate( '2018-12-31' ), Campaign::ACTIVE, Campaign::NEEDS_URL_KEY );
		$inActiveCampaign = new Campaign( 'test', 't', new CampaignDate( '2018-10-01' ), new CampaignDate( '2018-12-31' ), Campaign::ACTIVE, Campaign::NEEDS_NO_URL_KEY );

		$this->assertTrue( $activeCampaign->isOnlyActiveWithUrlKey() );
		$this->assertFalse( $inActiveCampaign->isOnlyActiveWithUrlKey() );
	}
}
