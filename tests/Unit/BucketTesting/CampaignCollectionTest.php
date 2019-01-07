<?php

declare(strict_types = 1);

namespace WMDE\Fundraising\Frontend\Tests\Unit\BucketTesting;

use PHPUnit\Framework\TestCase;
use WMDE\Fundraising\Frontend\BucketTesting\Bucket;
use WMDE\Fundraising\Frontend\BucketTesting\Campaign;
use WMDE\Fundraising\Frontend\BucketTesting\CampaignCollection;
use WMDE\Fundraising\Frontend\BucketTesting\CampaignDate;

/**
 * @covers \WMDE\Fundraising\Frontend\BucketTesting\CampaignCollection
 */
class CampaignCollectionTest extends TestCase {
	/** @var Campaign */
	private $firstCampaign;
	private $defaultBucketOfFirstCampaign;
	private $alternativeBucketOfFirstCampaign;
	/** @var Campaign */
	private $secondCampaign;
	private $defaultBucketOfSecondCampaign;
	private $alternativeBucketOfSecondCampaign;

	protected function setUp() {
		$this->firstCampaign = new Campaign(
			'test_something',
			't1',
			new CampaignDate(),
			( new CampaignDate() )->add( new \DateInterval( 'P3M' ) ),
			Campaign::ACTIVE
		);
		$this->defaultBucketOfFirstCampaign = new Bucket( 'a', $this->firstCampaign, Bucket::DEFAULT );
		$this->alternativeBucketOfFirstCampaign = new Bucket( 'b', $this->firstCampaign, Bucket::NON_DEFAULT );
		$this->firstCampaign
			->addBucket( $this->defaultBucketOfFirstCampaign )
			->addBucket( $this->alternativeBucketOfFirstCampaign );

		$this->secondCampaign = new Campaign(
			'five_year_plan',
			't2',
			new CampaignDate(),
			( new CampaignDate() )->add( new \DateInterval( 'P5Y' ) ),
			Campaign::ACTIVE
		);
		$this->defaultBucketOfSecondCampaign = new Bucket( 'a', $this->secondCampaign, Bucket::DEFAULT );
		$this->alternativeBucketOfSecondCampaign = new Bucket( 'b', $this->secondCampaign, Bucket::NON_DEFAULT );
		$this->secondCampaign
			->addBucket( $this->defaultBucketOfSecondCampaign )
			->addBucket( $this->alternativeBucketOfSecondCampaign );
	}

	public function testGivenCampaigns_itCanIterateOverThem() {
		$collection = new CampaignCollection( $this->firstCampaign, $this->secondCampaign );
		$iterator = $collection->getIterator();

		$this->assertSame( $this->firstCampaign, $iterator->current() );
		$iterator->next();
		$this->assertSame( $this->secondCampaign, $iterator->current() );
		$iterator->next();
		$this->assertFalse( $iterator->valid() );
	}

	public function testGivenActiveCampaigns_itCanSelectTheOneWithTheMostDistantEndDate() {
		$collection = new CampaignCollection( $this->firstCampaign, $this->secondCampaign );

		$this->assertSame( $this->secondCampaign, $collection->getMostDistantCampaign() );
	}

	public function testGivenInActiveCampaigns_itCanSelectTheOneWithTheMostDistantEndDate() {
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

	public function testGivenOnlyInactiveCampaigs_itWillSelectNoneAsMostDistant() {
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

	public function testGivenOnlyCampaigsInThePast_itWillSelectNoneAsMostDistant() {
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
