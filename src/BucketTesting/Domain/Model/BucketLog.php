<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\BucketTesting\Domain\Model;

use DateTime;
use DateTimeInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

class BucketLog {

	private int $id;
	private int $externalId;
	private string $eventName;
	private Collection $buckets;
	private DateTimeInterface $date;

	public function __construct( int $externalId, string $eventName ) {
		$this->externalId = $externalId;
		$this->eventName = $eventName;
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

	public function getBuckets(): Collection {
		return $this->buckets;
	}
}
