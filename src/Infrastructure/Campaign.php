<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Infrastructure;

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
	private $defaultGroup;
	private $urlKey;

	public const ACTIVE = true;
	public const INACTIVE = false;

	public function __construct( string $name, string $urlKey, \DateTime $startTimestamp, \DateTime $endTimestamp, bool $isActive,
			string $defaultGroup, array $groups ) {
		$this->name = $name;
		$this->urlKey = $urlKey;
		$this->active = $isActive;
		$this->startTimestamp = $startTimestamp;
		$this->endTimestamp = $endTimestamp;
		$this->defaultGroup = $defaultGroup;
		$this->groups = $groups;
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

	public function getDefaultGroup(): string {
		return $this->defaultGroup;
	}

	/**
	 * @return string[]
	 */
	public function getGroups(): array {
		return $this->groups;
	}

	public function getUrlKey(): string {
		return $this->urlKey;
	}

}
