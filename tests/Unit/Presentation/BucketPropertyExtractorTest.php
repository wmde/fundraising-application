<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\Unit\Presentation;

use PHPUnit\Framework\TestCase;
use WMDE\Fundraising\Frontend\BucketTesting\Domain\Model\Bucket;
use WMDE\Fundraising\Frontend\BucketTesting\Domain\Model\Campaign;
use WMDE\Fundraising\Frontend\BucketTesting\Domain\Model\CampaignDate;
use WMDE\Fundraising\Frontend\Presentation\BucketPropertyExtractor;

/**
 * @covers \WMDE\Fundraising\Frontend\Presentation\BucketPropertyExtractor
 */
class BucketPropertyExtractorTest extends TestCase {
	private Campaign $campaign1;
	private Campaign $campaign2;

	protected function setUp(): void {
		$now = new CampaignDate();
		$start = $now->modify( '-1 week' );
		$end = $now->modify( '+1 week' );
		$this->campaign1 = new Campaign( 'first_campaign', 'f', $start, $end, true );
		$this->campaign2 = new Campaign( 'second_campaign', 's', $start, $end, true );
	}

	public function testGivenNoBuckets_itReturnsEmptyArray(): void {
		$this->assertSame( [], BucketPropertyExtractor::listBucketIds() );
	}

	public function testGivenOneBucket_itReturnsItsId(): void {
		$templateParams = BucketPropertyExtractor::listBucketIds(
			new Bucket( 'default', $this->campaign1, true )
		);

		$this->assertEquals( [ 'campaigns.first_campaign.default' ], $templateParams );
	}

	public function testMultipleBuckets_itReturnsTheirId(): void {
		$templateParams = BucketPropertyExtractor::listBucketIds(
			new Bucket( 'default', $this->campaign1, true ),
			new Bucket( 'default', $this->campaign2, true )
		);
		$expectedParams = [
			'campaigns.first_campaign.default',
			'campaigns.second_campaign.default',
		];

		$this->assertEquals( $expectedParams, $templateParams );
	}

}
