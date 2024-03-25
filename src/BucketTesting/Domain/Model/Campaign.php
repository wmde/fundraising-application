<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\BucketTesting\Domain\Model;

/**
 * Value object for defining campaigns
 */
class Campaign {

	private bool $active;
	/**
	 * @var Bucket[]
	 */
	private array $buckets;
	private int $defaultBucketIndex;

	public const ACTIVE = true;
	public const INACTIVE = false;

	public const NEEDS_URL_KEY = true;
	public const NEEDS_NO_URL_KEY = false;

	public function __construct(
		private readonly string $name,
		private readonly string $urlKey,
		private readonly CampaignDate $startTimestamp,
		private readonly CampaignDate $endTimestamp,
		bool $isActive,
		private readonly bool $onlyActiveWithUrlKey = false
	) {
		$this->active = $isActive;
		$this->buckets = [];
		$this->defaultBucketIndex = -1;
	}

	public function isActive(): bool {
		return $this->active;
	}

	public function getStartTimestamp(): CampaignDate {
		return $this->startTimestamp;
	}

	public function getEndTimestamp(): CampaignDate {
		return $this->endTimestamp;
	}

	public function getName(): string {
		return $this->name;
	}

	/**
	 * @return Bucket[]
	 */
	public function getBuckets(): array {
		return $this->buckets;
	}

	public function getUrlKey(): string {
		return $this->urlKey;
	}

	public function getBucketByIndex( int $index ): ?Bucket {
		return $this->getBuckets()[$index] ?? null;
	}

	public function getIndexByBucket( Bucket $bucket ): int {
		$index = array_search( $bucket, $this->getBuckets(), true );
		if ( $index === false ) {
			throw new \OutOfBoundsException();
		}
		return $index;
	}

	public function addBucket( Bucket $bucket ): self {
		$this->buckets[] = $bucket;
		if ( $bucket->isDefaultBucket() ) {
			$this->defaultBucketIndex = count( $this->buckets ) - 1;
		}
		return $this;
	}

	public function isExpired( CampaignDate $now ): bool {
		return $this->startTimestamp > $now || $this->endTimestamp < $now;
	}

	public function getDefaultBucket(): Bucket {
		$bucket = $this->getBucketByIndex( $this->defaultBucketIndex );
		if ( $bucket === null ) {
			throw new \LogicException( 'No default bucket was added to this campaign' );
		}
		return $bucket;
	}

	public function isOnlyActiveWithUrlKey(): bool {
		return $this->onlyActiveWithUrlKey;
	}

}
