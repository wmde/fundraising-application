<?php

declare(strict_types = 1);

namespace WMDE\Fundraising\Frontend\Tests\Unit\BucketTesting;

use PHPUnit\Framework\TestCase;
use WMDE\Fundraising\Frontend\BucketTesting\Bucket;
use WMDE\Fundraising\Frontend\BucketTesting\Campaign;
use WMDE\Fundraising\Frontend\BucketTesting\CampaignCollection;

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
			new \DateTime(),
			( new \DateTime() )->add( new \DateInterval( 'P3M' ) ),
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
			new \DateTime(),
			( new \DateTime() )->add( new \DateInterval( 'P5Y' ) ),
			Campaign::ACTIVE
		);
		$this->defaultBucketOfSecondCampaign = new Bucket( 'a', $this->secondCampaign, Bucket::DEFAULT );
		$this->alternativeBucketOfSecondCampaign = new Bucket( 'b', $this->secondCampaign, Bucket::NON_DEFAULT );
		$this->secondCampaign
			->addBucket( $this->defaultBucketOfSecondCampaign )
			->addBucket( $this->alternativeBucketOfSecondCampaign );
	}


	public function testGivenValidUrlAndValue_splittingReturnsBucket() {
		$collection = new CampaignCollection( $this->firstCampaign, $this->secondCampaign );

		$this->assertEquals(
			[ [ $this->defaultBucketOfFirstCampaign ], [ $this->secondCampaign ] ],
			$collection->splitBucketsFromCampaigns( [ 't1' => 0 ] )
		);
		$this->assertEquals(
			[ [ $this->defaultBucketOfFirstCampaign, $this->alternativeBucketOfSecondCampaign ], [] ],
			$collection->splitBucketsFromCampaigns( [ 't1' => 0, 't2' => 1 ] )
		);
	}

	public function testGivenInvalidUrlValue_splittingReturnsCampaigns() {
		$collection = new CampaignCollection( $this->firstCampaign, $this->secondCampaign );

		$this->assertEquals(
			[ [], [ $this->firstCampaign, $this->secondCampaign ] ],
			$collection->splitBucketsFromCampaigns( [ 't21' => 0 ] )
		);
		$this->assertEquals(
			[ [], [ $this->firstCampaign, $this->secondCampaign ] ],
			$collection->splitBucketsFromCampaigns( [ 't21' => 0, 't2' => 99 ] )
		);
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

	public function testGivenOnlyInactiveCampaigs_itWillSelectNone() {
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

	public function testGivenOnlyCampaigsInThePast_itWillSelectNone() {
		$pastFirstCampaign = new Campaign(
			$this->firstCampaign->getName(),
			$this->firstCampaign->getUrlKey(),
			( new \DateTime() )->sub( new \DateInterval( 'P15M' ) ),
			( new \DateTime() )->sub( new \DateInterval( 'P12M' ) ),
			Campaign::ACTIVE
		);
		$pastSecondCampaign = new Campaign(
			$this->secondCampaign->getName(),
			$this->secondCampaign->getUrlKey(),
			( new \DateTime() )->sub( new \DateInterval( 'P5M' ) ),
			( new \DateTime() )->sub( new \DateInterval( 'P2M' ) ),
			Campaign::ACTIVE
		);

		$collection = new CampaignCollection( $pastFirstCampaign, $pastSecondCampaign );

		$this->assertNull( $collection->getMostDistantCampaign() );
	}

}
