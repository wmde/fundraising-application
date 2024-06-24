<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\Unit\BucketTesting;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use WMDE\Fundraising\Frontend\BucketTesting\Domain\Model\Bucket;
use WMDE\Fundraising\Frontend\BucketTesting\Domain\Model\Campaign;
use WMDE\Fundraising\Frontend\BucketTesting\Domain\Model\CampaignDate;

#[CoversClass( Bucket::class )]
class BucketTest extends TestCase {

	private function newCampaign(): Campaign {
		return new Campaign(
			'test1',
			't1',
			new CampaignDate(),
			new CampaignDate(),
			Campaign::ACTIVE
		);
	}

	public function testGivenBucketForCampaign_itCanReturnCampaignParameters(): void {
		$campaign = $this->newCampaign();
		$bucketA = new Bucket( 'a', $campaign, Bucket::DEFAULT );
		$bucketB = new Bucket( 'b', $campaign, Bucket::NON_DEFAULT );

		$campaign->addBucket( $bucketA )->addBucket( $bucketB );

		$this->assertEquals( [ 't1' => 0 ], $bucketA->getParameters() );
		$this->assertEquals( [ 't1' => 1 ], $bucketB->getParameters() );
	}
}
