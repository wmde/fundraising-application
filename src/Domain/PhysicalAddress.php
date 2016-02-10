<?php

declare(strict_types = 1);

namespace WMDE\Fundraising\Frontend\Domain;

use WMDE\Fundraising\Frontend\FreezableValueObject;

/**
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 * @author Kai Nissen < kai.nissen@wikimedia.de >
 */
class PhysicalAddress {
	use FreezableValueObject;

	private $streetAddress = '';
	private $postalCode = '';
	private $city = '';
	private $countryCode = '';

	public function getStreetAddress(): string {
		return $this->streetAddress;
	}

	public function setStreetAddress( string $streetAddress ) {
		$this->assertIsWritable();
		$this->streetAddress = $streetAddress;
	}

	public function getPostalCode(): string {
		return $this->postalCode;
	}

	public function setPostalCode( string $postalCode ) {
		$this->assertIsWritable();
		$this->postalCode = $postalCode;
	}

	public function getCity(): string {
		return $this->city;
	}

	public function setCity( string $city ) {
		$this->assertIsWritable();
		$this->city = $city;
	}

	public function getCountryCode(): string {
		return $this->countryCode;
	}

	public function setCountryCode( string $countryCode ) {
		$this->assertIsWritable();
		$this->countryCode = $countryCode;
	}

}
