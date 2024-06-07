<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\BucketTesting\Domain\Model;

use DateTime;
use DateTimeInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

class BucketLog {

	/**
	 * ID automagically set by Doctrine ORM
	 *
	 * @var int
	 * @phpstan-ignore-next-line
	 */
	private int $id;
	/**
	 * @var Collection<int, BucketLogBucket>
	 */
	private Collection $buckets;
	private DateTimeInterface $date;

	public function __construct(
		private readonly int $externalId,
		private readonly string $eventName
	) {
		$this->buckets = new ArrayCollection();
		$this->date = new DateTime();
	}

	public function getId(): int {
		return $this->id;
	}

	public function getExternalId(): int {
		return $this->externalId;
	}

	public function getDate(): DateTimeInterface {
		return $this->date;
	}

	public function getEventName(): ?string {
		return $this->eventName;
	}

	/**
	 * This is only used in tests
	 *
	 * @return Collection<int, BucketLogBucket>
	 */
	public function getBuckets(): Collection {
		return $this->buckets;
	}

	public function addBucket( string $bucketName, string $campaign ): void {
		$this->buckets->add( new BucketLogBucket( $this, $bucketName, $campaign ) );
	}

	public function shouldBeLogged(): bool {
		return $this->getBuckets()->count() > 0;
	}

}
