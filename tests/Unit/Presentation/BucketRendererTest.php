<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\Unit\Presentation;

use PHPUnit\Framework\TestCase;
use WMDE\Fundraising\Frontend\BucketTesting\Bucket;
use WMDE\Fundraising\Frontend\BucketTesting\Campaign;
use WMDE\Fundraising\Frontend\BucketTesting\CampaignDate;
use WMDE\Fundraising\Frontend\Presentation\BucketRenderer;

/**
 * @covers \WMDE\Fundraising\Frontend\Presentation\BucketRenderer
 */
class BucketRendererTest extends TestCase {
	private $campaign1;
	private $campaign2;

	protected function setUp(): void {
		$now = new CampaignDate();
		$start = $now->modify( '-1 week' );
		$end = $now->modify( '+1 week' );
		$this->campaign1 = new Campaign( 'first_campaign', 'f', $start, $end, true );
		$this->campaign2 = new Campaign( 'second_campaign', 's', $start, $end, true );
	}

	public function testGivenNoBuckets_itReturnsEmptyArray() {
		$this->assertSame( [], BucketRenderer::renderBuckets() );
	}

	public function testGivenOneBucket_itReturnsItsId() {
		$templateParams = BucketRenderer::renderBuckets(
			new Bucket( 'default', $this->campaign1, true )
		);

		$this->assertEquals( [ 'campaigns.first_campaign.default' ], $templateParams );
	}

	public function testMultipleOneBuckets_itReturnsTheirId() {
		$templateParams = BucketRenderer::renderBuckets(
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
