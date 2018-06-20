<?php

namespace WMDE\Fundraising\Frontend\Tests\Unit\Infrastructure;

use PHPUnit\Framework\TestCase;
use WMDE\Fundraising\Frontend\Infrastructure\BucketTesting\Bucket;
use WMDE\Fundraising\Frontend\Infrastructure\BucketTesting\BucketSelector;
use WMDE\Fundraising\Frontend\Infrastructure\BucketTesting\Campaign;
use WMDE\Fundraising\Frontend\Infrastructure\BucketTesting\CampaignCollection;

class BucketSelectorTest extends TestCase {

	public function testGivenNoCampaigns_getBucketNamesReturnsEmptyArray() {
		$this->assertSame( [], ( new BucketSelector ( new CampaignCollection(), [], [] ) )->selectBuckets() );
	}

	public function testGivenMatchingUrlParams_bucketIsSelected() {
		$campaign1 = $this->newCampaign();
		$bucketA = new Bucket( 'a', $campaign1, Bucket::DEFAULT );
		$bucketB = new Bucket( 'b', $campaign1, Bucket::NON_DEFAULT );
		$campaign1->addBucket( $bucketA )->addBucket( $bucketB );
		$this->assertSame(
			[ $bucketA ],
			( new BucketSelector (
				new CampaignCollection( $campaign1 ),
				[],
				[ 't1' => 0 ]
			) )->selectBuckets()
		);
	}

	private function newCampaign() {
		return new Campaign(
			'test1',
			't1',
			new \DateTime(),
			new \DateTime(),
			Campaign::ACTIVE
		);
	}

	public function testGivenMatchingCookieParams_bucketIsSelected() {
		$campaign1 = $this->newCampaign();
		$bucketA = new Bucket( 'a', $campaign1, Bucket::DEFAULT );
		$bucketB = new Bucket( 'b', $campaign1, Bucket::NON_DEFAULT );
		$campaign1->addBucket( $bucketA )->addBucket( $bucketB );
		$this->assertSame(
			[ $bucketA ],
			( new BucketSelector (
				new CampaignCollection( $campaign1 ),
				[ 't1' => 0 ],
				[]
			) )->selectBuckets()
		);
	}

	public function testGivenNoParams_bucketIsRandomlySelected() {
		$campaign1 = $this->newCampaign();
		$bucketA = new Bucket( 'a', $campaign1, Bucket::DEFAULT );
		$bucketB = new Bucket( 'b', $campaign1, Bucket::NON_DEFAULT );
		$campaign1->addBucket( $bucketA )->addBucket( $bucketB );
		$this->assertThat(
			( new BucketSelector (
				new CampaignCollection( $campaign1 ),
				[],
				[]
			) )->selectBuckets(),
			$this->logicalOr(
				$this->equalTo( [ $bucketA ] ),
				$this->equalTo( [ $bucketB ] )
			)
		);
	}

	public function testGivenAssignedBucket_campaignParametersAreSet() {
		$campaign1 = $this->newCampaign();
		$bucketA = new Bucket( 'a', $campaign1, Bucket::DEFAULT );
		$bucketB = new Bucket( 'b', $campaign1, Bucket::NON_DEFAULT );
		$campaign1->addBucket( $bucketA )->addBucket( $bucketB );
		$this->assertEquals( [ 't1' => 0 ], $bucketA->getParameters() );
	}

}
