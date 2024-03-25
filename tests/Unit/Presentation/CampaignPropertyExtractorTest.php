<?php
declare( strict_types=1 );

namespace WMDE\Fundraising\Frontend\Tests\Unit\Presentation;

use PHPUnit\Framework\TestCase;
use WMDE\Fundraising\Frontend\BucketTesting\Domain\Model\Campaign;
use WMDE\Fundraising\Frontend\BucketTesting\Domain\Model\CampaignDate;
use WMDE\Fundraising\Frontend\Presentation\CampaignPropertyExtractor;

/**
 * @covers \WMDE\Fundraising\Frontend\Presentation\CampaignPropertyExtractor
 */
class CampaignPropertyExtractorTest extends TestCase {
	public function testListURLKeysWithNoCampaigns(): void {
		$this->assertSame( [], CampaignPropertyExtractor::listURLKeys() );
	}

	public function testListURLKeysWithMultipleCampaigns(): void {
		$urlKeys = CampaignPropertyExtractor::listURLKeys( ...$this->givenActiveAndInactiveCampaigns() );
		$this->assertCount( 4, $urlKeys );
		$this->assertSame( [ 'f', 's', 'i', 'e' ], $urlKeys );
	}

	/**
	 * @return Campaign[]
	 */
	private function givenActiveAndInactiveCampaigns(): array {
		$now = new CampaignDate();
		$start = $now->modify( '-2 week' );
		$end = $now->modify( '+1 week' );
		$expiredEnd = $now->modify( '-1 week' );
		$campaign1 = new Campaign( 'first_campaign', 'f', $start, $end, true );
		$campaign2 = new Campaign( 'second_campaign', 's', $start, $end, true );
		$campaign3 = new Campaign( 'inactive_third_campaign', 'i', $start, $end, false );
		$campaign4 = new Campaign( 'expired_fourth_campaign', 'e', $start, $expiredEnd, true );
		return [ $campaign1, $campaign2, $campaign3, $campaign4 ];
	}
}
