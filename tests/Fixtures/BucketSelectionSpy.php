<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\Fixtures;

use WMDE\Fundraising\Frontend\BucketTesting\Bucket;
use WMDE\Fundraising\Frontend\BucketTesting\BucketSelectionStrategy;
use WMDE\Fundraising\Frontend\BucketTesting\Campaign;

class BucketSelectionSpy implements BucketSelectionStrategy {

	private $bucketSelector;
	private $bucketSelections;

	public function __construct( BucketSelectionStrategy $bucketSelector ) {
		$this->bucketSelector = $bucketSelector;
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