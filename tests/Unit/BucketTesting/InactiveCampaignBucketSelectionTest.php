<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\Unit\BucketTesting;

use WMDE\Fundraising\Frontend\BucketTesting\Bucket;
use WMDE\Fundraising\Frontend\BucketTesting\Campaign;
use WMDE\Fundraising\Frontend\BucketTesting\CampaignDate;
use WMDE\Fundraising\Frontend\BucketTesting\InactiveCampaignBucketSelection;
use PHPUnit\Framework\TestCase;

class InactiveCampaignBucketSelectionTest extends TestCase {

	/** @var CampaignDate */
	private $now;

	protected function setUp() {
		$this->now = new CampaignDate();
	}

	public function testGivenAnInactiveCampaign_itSelectsDefaultBucket() {
		$campaign = new Campaign(
			'test1',
			't1',
			( new CampaignDate() )->sub( new \DateInterval( 'P1M' ) ),
			( new CampaignDate() )->add( new \DateInterval( 'P1M' ) ),
			Campaign::INACTIVE
		);
		$defaultBucket = new Bucket( 'bucket1', $campaign, Bucket::DEFAULT );
		$campaign
			->addBucket( $defaultBucket )
			->addBucket( new Bucket( 'bucket2', $campaign, Bucket::NON_DEFAULT ) );

		$selectionStrategy = new InactiveCampaignBucketSelection( new CampaignDate() );

		$this->assertSame( $defaultBucket, $selectionStrategy->selectBucketForCampaign( $campaign ) );
	}

	public function testGivenAnExpiredCampaign_itSelectsDefaultBucket() {
		$campaign = new Campaign(
			'test1',
			't1',
			( new CampaignDate() )->sub( new \DateInterval( 'P2M' ) ),
			( new CampaignDate() )->sub( new \DateInterval( 'P1M' ) ),
			Campaign::ACTIVE
		);
		$defaultBucket = new Bucket( 'bucket1', $campaign, Bucket::DEFAULT );
		$campaign
			->addBucket( $defaultBucket )
			->addBucket( new Bucket( 'bucket2', $campaign, Bucket::NON_DEFAULT ) );

		$selectionStrategy = new InactiveCampaignBucketSelection( new CampaignDate() );

		$this->assertSame( $defaultBucket, $selectionStrategy->selectBucketForCampaign( $campaign ) );
	}

	public function testGivenAnActiveCampaign_itSelectsNoBucket() {
		$campaign = new Campaign(
			'test1',
			't1',
			( new CampaignDate() )->sub( new \DateInterval( 'P1M' ) ),
			( new CampaignDate() )->add( new \DateInterval( 'P1M' ) ),
			Campaign::ACTIVE
		);
		$campaign
			->addBucket( new Bucket( 'bucket1', $campaign, Bucket::DEFAULT ) )
			->addBucket( new Bucket( 'bucket2', $campaign, Bucket::NON_DEFAULT ) );

		$selectionStrategy = new InactiveCampaignBucketSelection( new CampaignDate() );

		$this->assertNull( $selectionStrategy->selectBucketForCampaign( $campaign ) );
	}

}
