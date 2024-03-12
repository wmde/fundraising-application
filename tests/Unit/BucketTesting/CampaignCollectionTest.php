<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\Unit\BucketTesting;

use PHPUnit\Framework\TestCase;
use WMDE\Fundraising\Frontend\BucketTesting\CampaignCollection;
use WMDE\Fundraising\Frontend\BucketTesting\Domain\Model\Bucket;
use WMDE\Fundraising\Frontend\BucketTesting\Domain\Model\Campaign;
use WMDE\Fundraising\Frontend\BucketTesting\Domain\Model\CampaignDate;

/**
 * @covers \WMDE\Fundraising\Frontend\BucketTesting\CampaignCollection
 */
class CampaignCollectionTest extends TestCase {
	private Campaign $firstCampaign;
	private Campaign $secondCampaign;

	protected function setUp(): void {
		$this->firstCampaign = new Campaign(
			'test_something',
			't1',
			new CampaignDate(),
			( new CampaignDate() )->add( new \DateInterval( 'P3M' ) ),
			Campaign::ACTIVE
		);
		$defaultBucketOfFirstCampaign = new Bucket( 'a', $this->firstCampaign, Bucket::DEFAULT );
		$alternativeBucketOfFirstCampaign = new Bucket( 'b', $this->firstCampaign, Bucket::NON_DEFAULT );
		$this->firstCampaign
			->addBucket( $defaultBucketOfFirstCampaign )
			->addBucket( $alternativeBucketOfFirstCampaign );

		$this->secondCampaign = new Campaign(
			'five_year_plan',
			't2',
			new CampaignDate(),
			( new CampaignDate() )->add( new \DateInterval( 'P5Y' ) ),
			Campaign::ACTIVE
		);
		$defaultBucketOfSecondCampaign = new Bucket( 'a', $this->secondCampaign, Bucket::DEFAULT );
		$alternativeBucketOfSecondCampaign = new Bucket( 'b', $this->secondCampaign, Bucket::NON_DEFAULT );
		$this->secondCampaign
			->addBucket( $defaultBucketOfSecondCampaign )
			->addBucket( $alternativeBucketOfSecondCampaign );
	}

	public function testGivenCampaigns_itCanIterateOverThem(): void {
		$collection = new CampaignCollection( $this->firstCampaign, $this->secondCampaign );
		$iterator = $collection->getIterator();

		$this->assertSame( $this->firstCampaign, $iterator->current() );
		$iterator->next();
		$this->assertSame( $this->secondCampaign, $iterator->current() );
		$iterator->next();
		$this->assertFalse( $iterator->valid() );
	}

	public function testGivenActiveCampaigns_itCanSelectTheOneWithTheMostDistantEndDate(): void {
		$collection = new CampaignCollection( $this->firstCampaign, $this->secondCampaign );

		$this->assertSame( $this->secondCampaign, $collection->getMostDistantCampaign() );
	}

	public function testGivenInActiveCampaigns_itCanSelectTheOneWithTheMostDistantEndDate(): void {
		$inactiveSecondCampaign = new Campaign(
			$this->secondCampaign->getName(),
			$this->secondCampaign->getUrlKey(),
			$this->secondCampaign->getStartTimestamp(),
			$this->secondCampaign->getEndTimestamp(),
			Campaign::INACTIVE
		);
		$collection = new CampaignCollection( $this->firstCampaign, $inactiveSecondCampaign );

		$this->assertSame( $this->firstCampaign, $collection->getMostDistantCampaign() );
	}

	public function testGivenOnlyInactiveCampaigs_itWillSelectNoneAsMostDistant(): void {
		$inactiveFirstCampaign = new Campaign(
			$this->firstCampaign->getName(),
			$this->firstCampaign->getUrlKey(),
			$this->firstCampaign->getStartTimestamp(),
			$this->firstCampaign->getEndTimestamp(),
			Campaign::INACTIVE
		);
		$collection = new CampaignCollection( $inactiveFirstCampaign );

		$this->assertNull( $collection->getMostDistantCampaign() );
	}

	public function testGivenOnlyCampaigsInThePast_itWillSelectNoneAsMostDistant(): void {
		$pastFirstCampaign = new Campaign(
			$this->firstCampaign->getName(),
			$this->firstCampaign->getUrlKey(),
			( new CampaignDate() )->sub( new \DateInterval( 'P15M' ) ),
			( new CampaignDate() )->sub( new \DateInterval( 'P12M' ) ),
			Campaign::ACTIVE
		);
		$pastSecondCampaign = new Campaign(
			$this->secondCampaign->getName(),
			$this->secondCampaign->getUrlKey(),
			( new CampaignDate() )->sub( new \DateInterval( 'P5M' ) ),
			( new CampaignDate() )->sub( new \DateInterval( 'P2M' ) ),
			Campaign::ACTIVE
		);

		$collection = new CampaignCollection( $pastFirstCampaign, $pastSecondCampaign );

		$this->assertNull( $collection->getMostDistantCampaign() );
	}

}
