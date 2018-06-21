<?php

declare(strict_types = 1);

namespace WMDE\Fundraising\Frontend\Tests\Unit\BucketTesting;

use PHPUnit\Framework\TestCase;
use WMDE\Fundraising\Frontend\BucketTesting\Bucket;
use WMDE\Fundraising\Frontend\BucketTesting\BucketSelector;
use WMDE\Fundraising\Frontend\BucketTesting\Campaign;
use WMDE\Fundraising\Frontend\BucketTesting\CampaignCollection;

/**
 * Class BucketSelectorTest
 * @covers WMDE\Fundraising\Frontend\BucketTesting\BucketSelector
 */
class BucketSelectorTest extends TestCase {

	private function newCampaign() {
		return new Campaign(
			'test1',
			't1',
			new \DateTime(),
			new \DateTime(),
			Campaign::ACTIVE
		);
	}

	private function createDefaultBucket( $campaign ) {
		return new Bucket( 'a', $campaign, Bucket::DEFAULT );
	}

	private function createNonDefaultBucket( $campaign ) {
		return new Bucket( 'b', $campaign, Bucket::NON_DEFAULT );
	}

	public function testGivenNoCampaigns_getBucketNamesReturnsEmptyArray() {
		$this->assertSame( [], ( new BucketSelector( new CampaignCollection() ) )->selectBuckets( [], [] ) );
	}

	public function testGivenMatchingUrlParams_bucketIsSelected() {
		$campaign = $this->newCampaign();
		$bucketA = $this->createDefaultBucket( $campaign );
		$bucketB = $this->createNonDefaultBucket( $campaign );

		$campaign->addBucket( $bucketA )->addBucket( $bucketB );

		$this->assertSame(
			[ $bucketA ],
			( new BucketSelector(
				new CampaignCollection( $campaign )
			) )->selectBuckets( [], [ 't1' => 0 ] )
		);
		$this->assertSame(
			[ $bucketB ],
			( new BucketSelector(
				new CampaignCollection( $campaign )
			) )->selectBuckets( [], [ 't1' => 1 ] )
		);
	}

	public function testGivenMatchingCookieParams_bucketIsSelected() {
		$campaign = $this->newCampaign();
		$bucketA = $this->createDefaultBucket( $campaign );
		$bucketB = $this->createNonDefaultBucket( $campaign );

		$campaign->addBucket( $bucketA )->addBucket( $bucketB );

		$this->assertSame(
			[ $bucketA ],
			( new BucketSelector(
				new CampaignCollection( $campaign )
			) )->selectBuckets( [ 't1' => 0 ], [] )
		);
		$this->assertSame(
			[ $bucketB ],
			( new BucketSelector(
				new CampaignCollection( $campaign )
			) )->selectBuckets( [ 't1' => 1 ], [] )
		);
	}

	public function testGivenNoParams_bucketIsRandomlySelected() {
		$campaign = $this->newCampaign();
		$bucketA = $this->createDefaultBucket( $campaign );
		$bucketB = $this->createNonDefaultBucket( $campaign );

		$campaign->addBucket( $bucketA )->addBucket( $bucketB );

		$this->assertThat(
			( new BucketSelector(
				new CampaignCollection( $campaign )
			) )->selectBuckets( [], [] ),
			$this->logicalOr(
				$this->equalTo( [ $bucketA ] ),
				$this->equalTo( [ $bucketB ] )
			)
		);
	}

	public function testGivenInvalidParams_bucketIsRandomlySelected() {
		$campaign = $this->newCampaign();
		$bucketA = $this->createDefaultBucket( $campaign );
		$bucketB = $this->createNonDefaultBucket( $campaign );

		$campaign->addBucket( $bucketA )->addBucket( $bucketB );

		$this->assertThat(
			( new BucketSelector(
				new CampaignCollection( $campaign )
			) )->selectBuckets( [ 't2'=> 0 ], [ 't1' => 'abc' ] ),
			$this->logicalOr(
				$this->equalTo( [ $bucketA ] ),
				$this->equalTo( [ $bucketB ] )
			)
		);
	}

	public function testGivenUrlAndCookieParameters_urlOverridesCookie() {
		$campaign = $this->newCampaign();
		$bucketA = $this->createDefaultBucket( $campaign );
		$bucketB = $this->createNonDefaultBucket( $campaign );
		$bucketSelector = new BucketSelector( new CampaignCollection( $campaign ) );

		$campaign->addBucket( $bucketA )->addBucket( $bucketB );

		$this->assertEquals( [ 't1' => 1 ], $bucketSelector->selectBuckets( [ 't1' => 0 ], [ 't1' => 1 ] )[0]->getParameters() );
	}

	public function testGivenInvalidUrlParameters_parametersAreSanitized() {
		$campaign = $this->newCampaign();
		$bucketA = $this->createDefaultBucket( $campaign );
		$bucketB = $this->createNonDefaultBucket( $campaign );
		$bucketSelector = new BucketSelector( new CampaignCollection( $campaign ) );

		$campaign->addBucket( $bucketA )->addBucket( $bucketB );

		$this->assertEquals( [ 't1' => 1 ], $bucketSelector->selectBuckets( [], [ 't1' => '1' ] )[0]->getParameters() );
	}

	public function testGivenInvalidCookieParameters_parametersAreSanitized() {
		$campaign = $this->newCampaign();
		$bucketA = $this->createDefaultBucket( $campaign );
		$bucketB = $this->createNonDefaultBucket( $campaign );
		$bucketSelector = new BucketSelector( new CampaignCollection( $campaign ) );

		$campaign->addBucket( $bucketA )->addBucket( $bucketB );

		$this->assertEquals( [ 't1' => 1 ], $bucketSelector->selectBuckets( [ 't1' => '1' ], [] )[0]->getParameters() );
	}

}
