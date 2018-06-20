<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Infrastructure;

use OutOfBoundsException;

/**
 * Value object for defining campaigns
 *
 * @licence GNU GPL v2+
 */
class Campaign {

	private $name;
	private $active;
	private $startTimestamp;
	private $endTimestamp;
	private $groups;
	private $urlKey;

	public const ACTIVE = true;
	public const INACTIVE = false;

	public function __construct( string $name, string $urlKey, \DateTime $startTimestamp, \DateTime $endTimestamp, bool $isActive ) {
		$this->name = $name;
		$this->urlKey = $urlKey;
		$this->active = $isActive;
		$this->startTimestamp = $startTimestamp;
		$this->endTimestamp = $endTimestamp;
		$this->groups = [];
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
	 * @return Group[]
	 */
	public function getGroups(): array {
		return $this->groups;
	}

	public function getUrlKey(): string {
		return $this->urlKey;
	}

	public function getGroupByIndex( int $index ): ?Group {
		return $this->getGroups()[$index] ?? null;
	}

	public function getIndexByGroup( Group $group ): int {
		$index = array_search( $group, $this->getGroups(), true );
		if ( $index === false ) {
			throw new OutOfBoundsException();
		}
		return $index;
	}

	public function addGroup( Group $group ): self {
		$this->groups[] = $group;
		return $this;
	}


}
