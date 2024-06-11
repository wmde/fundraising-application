<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\Unit\BucketTesting;

use PHPUnit\Framework\TestCase;
use WMDE\Fundraising\Frontend\BucketTesting\BucketSelector;
use WMDE\Fundraising\Frontend\BucketTesting\CampaignCollection;
use WMDE\Fundraising\Frontend\BucketTesting\Domain\Model\Bucket;
use WMDE\Fundraising\Frontend\BucketTesting\Domain\Model\Campaign;
use WMDE\Fundraising\Frontend\BucketTesting\Domain\Model\CampaignDate;
use WMDE\Fundraising\Frontend\BucketTesting\RandomBucketSelection;
use WMDE\Fundraising\Frontend\Tests\Fixtures\BucketSelectionSpy;

/**
 * @covers \WMDE\Fundraising\Frontend\BucketTesting\BucketSelector
 */
class BucketSelectorTest extends TestCase {

	private Bucket $defaultBucket;
	private Bucket $alternativeBucket;
	private CampaignCollection $campaignCollection;
	private BucketSelectionSpy $bucketSelectionStrategy;

	protected function setUp(): void {
		$campaign = new Campaign(
			'test1',
			't1',
			( new CampaignDate() )->sub( new \DateInterval( 'P1M' ) ),
			( new CampaignDate() )->add( new \DateInterval( 'P1M' ) ),
			Campaign::ACTIVE
		);
		$this->defaultBucket = new Bucket( 'a', $campaign, Bucket::DEFAULT );
		$this->alternativeBucket = new Bucket( 'b', $campaign, Bucket::NON_DEFAULT );
		// Add buckets in the right order, so default bucket has index 0 and alternative bucket has index 1
		$campaign
			->addBucket( $this->defaultBucket )
			->addBucket( $this->alternativeBucket );
		$this->campaignCollection = new CampaignCollection( $campaign );
		$this->bucketSelectionStrategy = new BucketSelectionSpy( new RandomBucketSelection() );
	}

	public function testGivenNoCampaigns_bucketSelectionIsEmptyArray(): void {
		$bucketSelector = new BucketSelector( new CampaignCollection(), new RandomBucketSelection() );

		$this->assertSame( [], $bucketSelector->selectBuckets( [] ) );
	}

	public function testGivenInactiveCampaign_defaultBucketIsSelected(): void {
		$campaign = new Campaign(
			'test1',
			't1',
			new CampaignDate(),
			new CampaignDate(),
			Campaign::INACTIVE
		);
		$defaultBucket = new Bucket( 'a', $campaign, Bucket::DEFAULT );
		$alternativeBucket = new Bucket( 'b', $campaign, Bucket::NON_DEFAULT );
		$campaign->addBucket( $defaultBucket )->addBucket( $alternativeBucket );
		$bucketSelector = new BucketSelector( new CampaignCollection( $campaign ), $this->bucketSelectionStrategy );

		$this->assertSame(
			[ $defaultBucket ],
			$bucketSelector->selectBuckets( [ 't1' => 0 ] )
		);
		$this->assertSame(
			[ $defaultBucket ],
			$bucketSelector->selectBuckets( [] )
		);
		$this->assertFalse( $this->bucketSelectionStrategy->bucketWasSelected(), 'Bucket not should be selected by selection strategy.' );
	}

	public function testGivenExpiredActiveCampaign_defaultBucketIsSelected(): void {
		$campaign = new Campaign(
			'test1',
			't1',
			( new CampaignDate() )->sub( new \DateInterval( 'P1M' ) ),
			( new CampaignDate() )->sub( new \DateInterval( 'P1D' ) ),
			Campaign::ACTIVE
		);
		$defaultBucket = new Bucket( 'a', $campaign, Bucket::DEFAULT );
		$alternativeBucket = new Bucket( 'b', $campaign, Bucket::NON_DEFAULT );
		$campaign->addBucket( $defaultBucket )->addBucket( $alternativeBucket );
		$bucketSelector = new BucketSelector( new CampaignCollection( $campaign ), $this->bucketSelectionStrategy );

		$this->assertSame(
			[ $defaultBucket ],
			$bucketSelector->selectBuckets( [ 't1' => 0 ] )
		);
		$this->assertSame(
			[ $defaultBucket ],
			$bucketSelector->selectBuckets( [] )
		);
		$this->assertFalse( $this->bucketSelectionStrategy->bucketWasSelected(), 'Bucket not should be selected by selection strategy.' );
	}

	public function testGivenMatchingUrlParams_bucketIsSelected(): void {
		$bucketSelector = new BucketSelector( $this->campaignCollection, $this->bucketSelectionStrategy );

		$this->assertSame(
			[ $this->defaultBucket ],
			$bucketSelector->selectBuckets( [ 't1' => 0 ] )
		);
		$this->assertSame(
			[ $this->alternativeBucket ],
			$bucketSelector->selectBuckets( [ 't1' => 1 ] )
		);
		$this->assertFalse( $this->bucketSelectionStrategy->bucketWasSelected(), 'Bucket should be selected by URL parameters' );
	}

	public function testGivenNoParamsAndActiveCampaign_bucketIsSelectedWithFallbackSelectionStrategy(): void {
		$bucketSelector = new BucketSelector( $this->campaignCollection, $this->bucketSelectionStrategy );

		$this->assertThat(
			$bucketSelector->selectBuckets( [] ),
			$this->logicalOr(
				$this->equalTo( [ $this->defaultBucket ] ),
				$this->equalTo( [ $this->alternativeBucket ] )
			)
		);
		$this->assertTrue( $this->bucketSelectionStrategy->bucketWasSelected(), 'Bucket should be selected by fallback selection strategy' );
	}

	public function testGivenNoParamsAndUrlActivatedCampaign_defaultBucketIsSelected(): void {
		$campaign = new Campaign(
			'test1',
			't1',
			( new CampaignDate() )->sub( new \DateInterval( 'P1M' ) ),
			( new CampaignDate() )->add( new \DateInterval( 'P1M' ) ),
			Campaign::ACTIVE,
			Campaign::NEEDS_URL_KEY
		);
		$defaultBucket = new Bucket( 'a', $campaign, Bucket::DEFAULT );
		$alternativeBucket = new Bucket( 'b', $campaign, Bucket::NON_DEFAULT );
		$campaign->addBucket( $defaultBucket )->addBucket( $alternativeBucket );
		$bucketSelector = new BucketSelector( new CampaignCollection( $campaign ), $this->bucketSelectionStrategy );

		$this->assertSame(
			[ $defaultBucket ],
			$bucketSelector->selectBuckets( [] ),
		);
		$this->assertFalse( $this->bucketSelectionStrategy->bucketWasSelected(), 'Bucket not should be selected by fallback selection strategy' );

		$this->assertSame(
			[ $alternativeBucket ],
			$bucketSelector->selectBuckets( [ 't1' => 1 ] ),
			);
		$this->assertFalse( $this->bucketSelectionStrategy->bucketWasSelected(), 'Bucket not should be selected by fallback selection strategy' );
	}

	/**
	 * @dataProvider invalidParametersProvider
	 *
	 * @param string $description
	 * @param array<string, int|string> $url
	 */
	public function testGivenInvalidParams_bucketIsSelectedWithFallbackSelectionStrategy( string $description, array $url ): void {
		$bucketSelector = new BucketSelector( $this->campaignCollection, $this->bucketSelectionStrategy );

		$this->assertThat(
			$bucketSelector->selectBuckets( $url ),
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

	/**
	 * @return iterable<array{string, array<string, int|string>}>
	 */
	public static function invalidParametersProvider(): iterable {
		yield [ 'unknown key in url', [ 't2' => 0 ] ];
		yield [ 'out of bounds index in url', [ 't1' => 2 ] ];
		yield [ 'non-numeric index in url', [ 't1' => 'lol' ] ];
		yield [ 'colorful mix', [ 't1' => 99, 'goats' => 1 ] ];
	}
}
