<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\BucketTesting;

/**
 * Value object for defining campaigns
 *
 * @license GNU GPL v2+
 */
class Campaign {

	private $name;
	private $active;
	private $startTimestamp;
	private $endTimestamp;
	private $buckets;
	private $urlKey;
	private $defaultBucketIndex;

	public const ACTIVE = true;
	public const INACTIVE = false;

	public function __construct( string $name, string $urlKey, \DateTime $startTimestamp, \DateTime $endTimestamp, bool $isActive ) {
		$this->name = $name;
		$this->urlKey = $urlKey;
		$this->active = $isActive;
		$this->startTimestamp = $startTimestamp;
		$this->endTimestamp = $endTimestamp;
		$this->buckets = [];
		$this->defaultBucketIndex = -1;
	}

	public function isActive(): bool {
		return $this->active;
	}

	public function getStartTimestamp(): \DateTime {
		return $this->startTimestamp;
	}

	public function getEndTimestamp(): \DateTime {
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

	public function isExpired( \DateTime $now ): bool {
		return $this->startTimestamp > $now || $this->endTimestamp < $now;
	}

	public function getDefaultBucket(): Bucket {
		$bucket = $this->getBucketByIndex( $this->defaultBucketIndex );
		if ( is_null( $bucket ) ) {
			throw new \LogicException( 'No default bucket was added to this campaign' );
		}
		return $bucket;
	}


}
