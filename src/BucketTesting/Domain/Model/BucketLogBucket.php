<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\BucketTesting\Domain\Model;

class BucketLogBucket {

	/**
	 * ID automagically set by Doctrine ORM
	 *
	 * @var int
	 * @phpstan-ignore-next-line
	 */
	private int $id;

	public function __construct(
		private BucketLog $bucketLog,
		private readonly string $name,
		private readonly string $campaign
	) {
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
