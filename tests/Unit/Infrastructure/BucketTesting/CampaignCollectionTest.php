<?php

namespace WMDE\Fundraising\Frontend\Tests\Unit\Infrastructure;

use PHPUnit\Framework\TestCase;
use WMDE\Fundraising\Frontend\Infrastructure\BucketTesting\Bucket;
use WMDE\Fundraising\Frontend\Infrastructure\BucketTesting\Campaign;
use WMDE\Fundraising\Frontend\Infrastructure\BucketTesting\CampaignCollection;

class CampaignCollectionTest extends TestCase {

	public function testGivenValidUrlAndValue_itReturnsBucket() {
		$campaign1 = new Campaign(
			'test1',
			't1',
			new \DateTime(),
			new \DateTime(),
			Campaign::ACTIVE
		);
		$bucketA = new Bucket( 'a', $campaign1, Bucket::DEFAULT );
		$bucketB = new Bucket( 'b', $campaign1, Bucket::NON_DEFAULT );
		$campaign1->addBucket( $bucketA )->addBucket( $bucketB );
		$campaign2 = new Campaign(
			'test2',
			't2',
			new \DateTime(),
			new \DateTime(),
			Campaign::ACTIVE
		);
		$bucketC = new Bucket( 'c', $campaign1, Bucket::DEFAULT );
		$bucketD = new Bucket( 'd', $campaign1, Bucket::NON_DEFAULT );
		$campaign2->addBucket( $bucketC )->addBucket( $bucketD );

		$collection = new CampaignCollection( $campaign1, $campaign2 );

		$this->assertEquals(
			[ [ $bucketA ], [ $campaign2 ] ],
			$collection->splitBucketsFromCampaigns( [ 't1' => 0 ] )
		);
		$this->assertEquals(
			[ [ $bucketA, $bucketD ], [] ],
			$collection->splitBucketsFromCampaigns( [ 't1' => 0, 't2' => 1 ] )
		);
	}

	public function testGivenInvalidUrlValue_itReturnsCampaigns() {
		$campaign1 = new Campaign(
			'test1',
			't1',
			new \DateTime(),
			new \DateTime(),
			Campaign::ACTIVE
		);
		$bucketA = new Bucket( 'a', $campaign1, Bucket::DEFAULT );
		$bucketB = new Bucket( 'b', $campaign1, Bucket::NON_DEFAULT );
		$campaign1->addBucket( $bucketA )->addBucket( $bucketB );
		$campaign2 = new Campaign(
			'test2',
			't2',
			new \DateTime(),
			new \DateTime(),
			Campaign::ACTIVE
		);
		$bucketC = new Bucket( 'c', $campaign1, Bucket::DEFAULT );
		$bucketD = new Bucket( 'd', $campaign1, Bucket::NON_DEFAULT );
		$campaign2->addBucket( $bucketC )->addBucket( $bucketD );

		$collection = new CampaignCollection( $campaign1, $campaign2 );

		$this->assertEquals(
			[ [], [ $campaign1, $campaign2 ] ],
			$collection->splitBucketsFromCampaigns( [ 't21' => 0 ] )
		);
		$this->assertEquals(
			[ [], [$campaign1, $campaign2] ],
			$collection->splitBucketsFromCampaigns( [ 't21' => 0, 't22' => 1 ] )
		);
	}

}
