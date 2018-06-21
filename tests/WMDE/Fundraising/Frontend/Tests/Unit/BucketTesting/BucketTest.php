<?php

namespace WMDE\Fundraising\Frontend\Tests\WMDE\Fundraising\Frontend\Tests\Unit\BucketTesting;

use WMDE\Fundraising\Frontend\BucketTesting\Bucket;
use PHPUnit\Framework\TestCase;
use WMDE\Fundraising\Frontend\BucketTesting\Campaign;

class BucketTest extends TestCase {
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

	public function testGivenAssignedBucket_campaignParametersAreSet() {
		$campaign = $this->newCampaign();
		$bucketA = $this->createDefaultBucket( $campaign );
		$bucketB = $this->createNonDefaultBucket( $campaign );

		$campaign->addBucket( $bucketA )->addBucket( $bucketB );

		$this->assertEquals( [ 't1' => 0 ], $bucketA->getParameters() );
	}
}
