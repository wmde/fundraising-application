<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\Unit\BucketTesting;

use PHPUnit\Framework\TestCase;
use WMDE\Fundraising\Frontend\BucketTesting\Campaign;
use WMDE\Fundraising\Frontend\BucketTesting\Bucket;

class CampaignTest extends TestCase {

	public function testBucketsAddedGetAnIndexInTheOrderTheyWereAdded() {
		$campaign = new Campaign( 'test', 't', new \DateTime(), new \DateTime(), true );
		$firstBucket = new Bucket( 'default', $campaign, Bucket::DEFAULT );
		$secondBucket = new Bucket( 'variant_1', $campaign, Bucket::NON_DEFAULT );
		$thirdBucket = new Bucket( 'variant_2', $campaign, Bucket::NON_DEFAULT );

		$campaign->addBucket( $firstBucket )->addBucket( $secondBucket )->addBucket( $thirdBucket );

		$this->assertSame( $firstBucket, $campaign->getBucketByIndex( 0 ) );
		$this->assertSame( $secondBucket, $campaign->getBucketByIndex( 1 ) );
		$this->assertSame( $thirdBucket, $campaign->getBucketByIndex( 2 ) );
	}

	public function testCampaignsCanReturnIndexesForBuckets() {
		$campaign = new Campaign( 'test', 't', new \DateTime(), new \DateTime(), true );
		$firstBucket = new Bucket( 'default', $campaign, Bucket::DEFAULT );
		$secondBucket = new Bucket( 'variant_1', $campaign, Bucket::NON_DEFAULT );
		$thirdBucket = new Bucket( 'variant_2', $campaign, Bucket::NON_DEFAULT );

		$campaign->addBucket( $firstBucket )->addBucket( $secondBucket )->addBucket( $thirdBucket );

		$this->assertSame( 0, $campaign->getIndexByBucket( $firstBucket ) );
		$this->assertSame( 1, $campaign->getIndexByBucket( $secondBucket ) );
		$this->assertSame( 2, $campaign->getIndexByBucket( $thirdBucket ) );
	}

	public function testGivenABucketThatIsNotAddedToCampaigns_campaignWillThrowAnException() {
		$campaign = new Campaign( 'test', 't', new \DateTime(), new \DateTime(), true );
		$firstBucket = new Bucket( 'default', $campaign, Bucket::DEFAULT );
		$secondBucket = new Bucket( 'variant_1', $campaign, Bucket::NON_DEFAULT );
		$thirdBucket = new Bucket( 'variant_2', $campaign, Bucket::NON_DEFAULT );

		$campaign->addBucket( $firstBucket )->addBucket( $secondBucket );

		$this->expectException( \OutOfBoundsException::class );
		$campaign->getIndexByBucket( $thirdBucket );
	}

	public function testGivenAnUnknownIndex_campaignWillReturnNull() {
		$campaign = new Campaign( 'test', 't', new \DateTime(), new \DateTime(), true );

		$this->assertNull( $campaign->getBucketByIndex( 0 ) );
	}
}
