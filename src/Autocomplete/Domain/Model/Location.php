<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Autocomplete\Domain\Model;

/**
 * We get a dataset from a third party with the structure described by this model.
 * It's mostly used in order for Doctrine to create and interact with the database table.
 */
class Location {

	public function __construct(
		private int $id,
		private string $stateName,
		private string $stateNutscode,
		private string $regionName,
		private string $regionNutscode,
		private string $districtName,
		private string $districtType,
		private string $districtNutscode,
		private string $communityName,
		private string $communityType,
		private string $communityKey,
		private string $regionKey,
		private float $communityLatitude,
		private float $communityLongitude,
		private string $cityId,
		private string $cityName,
		private float $cityLatitude,
		private float $cityLongitude,
		private string $postcode,
		private string $street ) {
	}

	public function getId(): int {
		return $this->id;
	}

	public function getStateName(): string {
		return $this->stateName;
	}

	public function getStateNutscode(): string {
		return $this->stateNutscode;
	}

	public function getRegionName(): string {
		return $this->regionName;
	}

	public function getRegionNutscode(): string {
		return $this->regionNutscode;
	}

	public function getDistrictName(): string {
		return $this->districtName;
	}

	public function getDistrictType(): string {
		return $this->districtType;
	}

	public function getDistrictNutscode(): string {
		return $this->districtNutscode;
	}

	public function getCommunityName(): string {
		return $this->communityName;
	}

	public function getCommunityType(): string {
		return $this->communityType;
	}

	public function getCommunityKey(): string {
		return $this->communityKey;
	}

	public function getRegionKey(): string {
		return $this->regionKey;
	}

	public function getCommunityLatitude(): float {
		return $this->communityLatitude;
	}

	public function getCommunityLongitude(): float {
		return $this->communityLongitude;
	}

	public function getCityId(): string {
		return $this->cityId;
	}

	public function getCityName(): string {
		return $this->cityName;
	}

	public function getCityLatitude(): float {
		return $this->cityLatitude;
	}

	public function getCityLongitude(): float {
		return $this->cityLongitude;
	}

	public function getPostcode(): string {
		return $this->postcode;
	}

	public function getStreet(): string {
		return $this->street;
	}
}
