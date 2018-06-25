<?php

declare(strict_types = 1);

namespace WMDE\Fundraising\Frontend\Tests\Unit\BucketTesting;

use PHPUnit\Framework\TestCase;
use WMDE\Fundraising\Frontend\BucketTesting\Bucket;
use WMDE\Fundraising\Frontend\BucketTesting\BucketSelector;
use WMDE\Fundraising\Frontend\BucketTesting\Campaign;
use WMDE\Fundraising\Frontend\BucketTesting\CampaignCollection;
use WMDE\Fundraising\Frontend\BucketTesting\RandomBucketSelection;
use WMDE\Fundraising\Frontend\Tests\Fixtures\BucketSelectionSpy;

/**
 * @covers \WMDE\Fundraising\Frontend\BucketTesting\BucketSelector
 */
class BucketSelectorTest extends TestCase {

	private $campaign;
	private $defaultBucket;
	private $alternativeBucket;
	private $campaignCollection;
	/**
	 * @var BucketSelectionSpy
	 */
	private $bucketSelectionStrategy;

	protected function setUp() {
		$this->campaign = new Campaign(
			'test1',
			't1',
			( new \DateTime() )->sub( new \DateInterval( 'P1M' ) ),
			( new \DateTime() )->add( new \DateInterval( 'P1M' ) ),
			Campaign::ACTIVE
		);
		$this->defaultBucket = new Bucket( 'a', $this->campaign, Bucket::DEFAULT );
		$this->alternativeBucket = new Bucket( 'b', $this->campaign, Bucket::NON_DEFAULT );
		$this->campaign
			->addBucket( $this->defaultBucket )      // default bucket has index 0
			->addBucket( $this->alternativeBucket ); // alternative bucket has index 1
		$this->campaignCollection = new CampaignCollection( $this->campaign );
		$this->bucketSelectionStrategy = new BucketSelectionSpy( new RandomBucketSelection() );
	}

	public function testGivenNoCampaigns_bucketSelectionIsEmptyArray() {
		$bucketSelector = new BucketSelector( new CampaignCollection(), new RandomBucketSelection() );

		$this->assertSame( [], $bucketSelector->selectBuckets( [], [] ) );
	}

	public function testGivenInactiveCampaign_defaultBucketIsSelected() {
		$campaign = new Campaign(
			'test1',
			't1',
			new \DateTime(),
			new \DateTime(),
			Campaign::INACTIVE
		);
		$defaultBucket = new Bucket( 'a', $campaign, Bucket::DEFAULT );
		$alternativeBucket = new Bucket( 'b', $campaign, Bucket::NON_DEFAULT );
		$campaign->addBucket( $defaultBucket )->addBucket( $alternativeBucket );
		$bucketSelector = new BucketSelector( new CampaignCollection( $campaign ), $this->bucketSelectionStrategy );

		$this->assertSame(
			[ $defaultBucket ],
			$bucketSelector->selectBuckets( [ 't1' => 0 ], [] )
		);
		$this->assertSame(
			[ $defaultBucket ],
			$bucketSelector->selectBuckets( [], [ 't1' => 0 ] )
		);
		$this->assertSame(
			[ $defaultBucket ],
			$bucketSelector->selectBuckets( [], [] )
		);
		$this->assertFalse( $this->bucketSelectionStrategy->bucketWasSelected(), 'Bucket not should be selected by selection strategy.' );
	}

	public function testGivenExpiredActiveCampaign_defaultBucketIsSelected() {
		$campaign = new Campaign(
			'test1',
			't1',
			( new \DateTime() )->sub( new \DateInterval( 'P1M' ) ),
			( new \DateTime() )->sub( new \DateInterval( 'P1D' ) ),
			Campaign::ACTIVE
		);
		$defaultBucket = new Bucket( 'a', $campaign, Bucket::DEFAULT );
		$alternativeBucket = new Bucket( 'b', $campaign, Bucket::NON_DEFAULT );
		$campaign->addBucket( $defaultBucket )->addBucket( $alternativeBucket );
		$bucketSelector = new BucketSelector( new CampaignCollection( $campaign ), $this->bucketSelectionStrategy );

		$this->assertSame(
			[ $defaultBucket ],
			$bucketSelector->selectBuckets( [ 't1' => 0 ], [] )
		);
		$this->assertSame(
			[ $defaultBucket ],
			$bucketSelector->selectBuckets( [], [ 't1' => 0 ] )
		);
		$this->assertSame(
			[ $defaultBucket ],
			$bucketSelector->selectBuckets( [], [] )
		);
		$this->assertFalse( $this->bucketSelectionStrategy->bucketWasSelected(), 'Bucket not should be selected by selection strategy.' );
	}

	public function testGivenMatchingUrlParams_bucketIsSelected() {
		$bucketSelector = new BucketSelector( $this->campaignCollection, $this->bucketSelectionStrategy );

		$this->assertSame(
			[ $this->defaultBucket ],
			$bucketSelector->selectBuckets( [], [ 't1' => 0 ] )
		);
		$this->assertSame(
			[ $this->alternativeBucket ],
			$bucketSelector->selectBuckets( [], [ 't1' => 1 ] )
		);
		$this->assertFalse( $this->bucketSelectionStrategy->bucketWasSelected(), 'Bucket should be selected by URL parameters' );
	}

	public function testGivenMatchingCookieParams_bucketIsSelected() {
		$bucketSelector = new BucketSelector( $this->campaignCollection, $this->bucketSelectionStrategy );

		$this->assertSame(
			[ $this->defaultBucket ],
			$bucketSelector->selectBuckets( [ 't1' => 0 ], [] )
		);
		$this->assertSame(
			[ $this->alternativeBucket ],
			$bucketSelector->selectBuckets( [ 't1' => 1 ], [] )
		);
		$this->assertFalse( $this->bucketSelectionStrategy->bucketWasSelected(), 'Bucket should be selected by Cookie parameters' );
	}

	public function testGivenUrlAndCookieParameters_urlOverridesCookie() {
		$bucketSelector = new BucketSelector( $this->campaignCollection, $this->bucketSelectionStrategy );

		$this->assertEquals( [ $this->alternativeBucket ], $bucketSelector->selectBuckets( [ 't1' => 0 ], [ 't1' => 1 ] ) );
	}

	public function testGivenNoParamsAndActiveCampaign_bucketIsSelectedWithFallbackSelectionStrategy() {
		$bucketSelector = new BucketSelector( $this->campaignCollection, $this->bucketSelectionStrategy );

		$this->assertThat(
			$bucketSelector->selectBuckets( [], [] ),
			$this->logicalOr(
				$this->equalTo( [ $this->defaultBucket ] ),
				$this->equalTo( [ $this->alternativeBucket ] )
			)
		);
		$this->assertTrue( $this->bucketSelectionStrategy->bucketWasSelected(), 'Bucket should be selected by fallback selection strategy' );
	}

	/**
	 * @dataProvider invalidParametersProvider
	 */
	public function testGivenInvalidParams_bucketIsSelectedWithFallbackSelectionStrategy( string $description, array $cookie, array $url ) {
		$bucketSelector = new BucketSelector( $this->campaignCollection, $this->bucketSelectionStrategy );

		$this->assertThat(
			$bucketSelector->selectBuckets( $cookie, $url ),
			$this->logicalOr(
				$this->equalTo( [ $this->defaultBucket ] ),
				$this->equalTo( [ $this->alternativeBucket ] )
			)
		);
		$this->assertTrue(
			$this->bucketSelectionStrategy->bucketWasSelected(),
			'Bucket should be selected by selection strategy. Failed for ' . $description
		);
	}

	public function invalidParametersProvider(): iterable {
		yield [ 'unknown key in url', [], [ 't2' => 0 ] ];
		yield [ 'unknown key in cookie', [ 't2' => 0 ], [] ];
		yield [ 'out of bounds index in url', [], [ 't1' => 2 ] ];
		yield [ 'out of bounds index in cookie', [ 't1' => 2 ], [] ];
		yield [ 'non-numeric index in url', [], [ 't1' => 'lol' ] ];
		yield [ 'non-numeric index in cookie', [ 't1' => 'cat' ], [] ];
		yield [ 'colorful mix', [ 't1' => 'cat', 't2' => 0 ], [ 't1' => 99, 'goats' => 1 ] ];
	}
}
