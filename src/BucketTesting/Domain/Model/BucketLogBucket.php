<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\BucketTesting\Domain\Model;

class BucketLogBucket {

	private int $id;
	private BucketLog $bucketLog;
	private string $name;
	private string $campaign;

	public function __construct( BucketLog $bucketLog, string $name, string $campaign ) {
		$this->bucketLog = $bucketLog;
		$this->name = $name;
		$this->campaign = $campaign;
	}

	public function getId(): int {
		return $this->id;
	}

	public function getBucketLog(): BucketLog {
		return $this->bucketLog;
	}

	public function getName(): string {
		return $this->name;
	}

	public function getCampaign(): string {
		return $this->campaign;
	}

	public function setBucketLog( BucketLog $bucketLog ): BucketLogBucket {
		$this->bucketLog = $bucketLog;
		return $this;
	}
}
