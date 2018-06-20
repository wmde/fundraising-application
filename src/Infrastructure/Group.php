<?php

namespace WMDE\Fundraising\Frontend\Infrastructure;

class Group {
	private $name;
	private $campaign;
	private $defaultGroup;
	const DEFAULT = true;
	const NON_DEFAULT = false;

	public function __construct( string $name, Campaign $campaign, bool $defaultGroup ) {
		$this->name = $name;
		$this->campaign = $campaign;
		$this->defaultGroup = $defaultGroup;
	}

	public function getName(): string {
		return $this->name;
	}

	public function getId(): string {
		return $this->getCampaign()->getName() . '.' . $this->getName();
	}

	public function getCampaign(): Campaign {
		return $this->campaign;
	}

	public function isDefaultGroup(): bool {
		return $this->defaultGroup;
	}

	public function getUrlParameter(): array {
		return [ $this->campaign->getUrlKey() => $this->campaign->getIndexByGroup( $this ) ];
	}

}