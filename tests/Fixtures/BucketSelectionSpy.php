<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\Fixtures;

use WMDE\Fundraising\Frontend\BucketTesting\BucketSelectionStrategy;
use WMDE\Fundraising\Frontend\BucketTesting\Domain\Model\Bucket;
use WMDE\Fundraising\Frontend\BucketTesting\Domain\Model\Campaign;

class BucketSelectionSpy implements BucketSelectionStrategy {
	/**
	 * @var Bucket[]
	 */
	private array $bucketSelections = [];

	public function __construct( private readonly BucketSelectionStrategy $bucketSelector ) {
		$this->bucketSelections = [];
	}

	public function selectBucketForCampaign( Campaign $campaign ): Bucket {
		$bucket = $this->bucketSelector->selectBucketForCampaign( $campaign );
		$this->bucketSelections[] = $bucket;
		return $bucket;
	}

	public function bucketWasSelected(): bool {
		return count( $this->bucketSelections ) > 0;
	}
}
