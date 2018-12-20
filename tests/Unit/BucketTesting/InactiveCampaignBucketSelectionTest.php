<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\Unit\BucketTesting;

use WMDE\Fundraising\Frontend\BucketTesting\Bucket;
use WMDE\Fundraising\Frontend\BucketTesting\Campaign;
use WMDE\Fundraising\Frontend\BucketTesting\InactiveCampaignBucketSelection;
use PHPUnit\Framework\TestCase;

class InactiveCampaignBucketSelectionTest extends TestCase {

	/** @var \DateTime */
	private $now;

	protected function setUp() {
		$this->now = new \DateTime();
	}

	public function testGivenAnInactiveCampaign_itSelectsDefaultBucket() {
		$campaign = new Campaign(
			'test1',
			't1',
			( new \DateTime() )->sub( new \DateInterval( 'P1M' ) ),
			( new \DateTime() )->add( new \DateInterval( 'P1M' ) ),
			Campaign::INACTIVE
		);
		$defaultBucket = new Bucket( 'bucket1', $campaign, Bucket::DEFAULT );
		$campaign
			->addBucket( $defaultBucket )
			->addBucket( new Bucket( 'bucket2', $campaign, Bucket::NON_DEFAULT ) );

		$selectionStrategy = new InactiveCampaignBucketSelection( new \DateTime() );

		$this->assertSame( $defaultBucket, $selectionStrategy->selectBucketForCampaign( $campaign ) );
	}

	public function testGivenAnExpiredCampaign_itSelectsDefaultBucket() {
		$campaign = new Campaign(
			'test1',
			't1',
			( new \DateTime() )->sub( new \DateInterval( 'P2M' ) ),
			( new \DateTime() )->sub( new \DateInterval( 'P1M' ) ),
			Campaign::ACTIVE
		);
		$defaultBucket = new Bucket( 'bucket1', $campaign, Bucket::DEFAULT );
		$campaign
			->addBucket( $defaultBucket )
			->addBucket( new Bucket( 'bucket2', $campaign, Bucket::NON_DEFAULT ) );

		$selectionStrategy = new InactiveCampaignBucketSelection( new \DateTime() );

		$this->assertSame( $defaultBucket, $selectionStrategy->selectBucketForCampaign( $campaign ) );
	}

	public function testGivenAnActiveCampaign_itSelectsNoBucket() {
		$campaign = new Campaign(
			'test1',
			't1',
			( new \DateTime() )->sub( new \DateInterval( 'P1M' ) ),
			( new \DateTime() )->add( new \DateInterval( 'P1M' ) ),
			Campaign::ACTIVE
		);
		$campaign
			->addBucket( new Bucket( 'bucket1', $campaign, Bucket::DEFAULT ) )
			->addBucket( new Bucket( 'bucket2', $campaign, Bucket::NON_DEFAULT ) );

		$selectionStrategy = new InactiveCampaignBucketSelection( new \DateTime() );

		$this->assertNull( $selectionStrategy->selectBucketForCampaign( $campaign ) );
	}

}
