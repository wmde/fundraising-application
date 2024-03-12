<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\Unit\BucketTesting;

use PHPUnit\Framework\TestCase;
use WMDE\Fundraising\Frontend\BucketTesting\Domain\Model\Bucket;
use WMDE\Fundraising\Frontend\BucketTesting\Domain\Model\Campaign;
use WMDE\Fundraising\Frontend\BucketTesting\Domain\Model\CampaignDate;
use WMDE\Fundraising\Frontend\BucketTesting\InactiveCampaignBucketSelection;

/**
 * @covers \WMDE\Fundraising\Frontend\BucketTesting\InactiveCampaignBucketSelection
 */
class InactiveCampaignBucketSelectionTest extends TestCase {

	private CampaignDate $now;

	protected function setUp(): void {
		$this->now = new CampaignDate();
	}

	public function testGivenAnInactiveCampaign_itSelectsDefaultBucket(): void {
		$campaign = new Campaign(
			'test1',
			't1',
			( $this->now )->sub( new \DateInterval( 'P1M' ) ),
			( $this->now )->add( new \DateInterval( 'P1M' ) ),
			Campaign::INACTIVE
		);
		$defaultBucket = new Bucket( 'bucket1', $campaign, Bucket::DEFAULT );
		$campaign
			->addBucket( $defaultBucket )
			->addBucket( new Bucket( 'bucket2', $campaign, Bucket::NON_DEFAULT ) );

		$selectionStrategy = new InactiveCampaignBucketSelection( $this->now );

		$this->assertSame( $defaultBucket, $selectionStrategy->selectBucketForCampaign( $campaign ) );
	}

	public function testGivenAnExpiredCampaign_itSelectsDefaultBucket(): void {
		$campaign = new Campaign(
			'test1',
			't1',
			( $this->now )->sub( new \DateInterval( 'P2M' ) ),
			( $this->now )->sub( new \DateInterval( 'P1M' ) ),
			Campaign::ACTIVE
		);
		$defaultBucket = new Bucket( 'bucket1', $campaign, Bucket::DEFAULT );
		$campaign
			->addBucket( $defaultBucket )
			->addBucket( new Bucket( 'bucket2', $campaign, Bucket::NON_DEFAULT ) );

		$selectionStrategy = new InactiveCampaignBucketSelection( $this->now );

		$this->assertSame( $defaultBucket, $selectionStrategy->selectBucketForCampaign( $campaign ) );
	}

	public function testGivenAnActiveCampaign_itSelectsNoBucket(): void {
		$campaign = new Campaign(
			'test1',
			't1',
			( $this->now )->sub( new \DateInterval( 'P1M' ) ),
			( $this->now )->add( new \DateInterval( 'P1M' ) ),
			Campaign::ACTIVE
		);
		$campaign
			->addBucket( new Bucket( 'bucket1', $campaign, Bucket::DEFAULT ) )
			->addBucket( new Bucket( 'bucket2', $campaign, Bucket::NON_DEFAULT ) );

		$selectionStrategy = new InactiveCampaignBucketSelection( $this->now );

		$this->assertNull( $selectionStrategy->selectBucketForCampaign( $campaign ) );
	}

}
