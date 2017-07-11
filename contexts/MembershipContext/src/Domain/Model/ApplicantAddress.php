<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\MembershipContext\Domain\Model;

use WMDE\Fundraising\Frontend\FreezableValueObject;

/**
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 * @author Kai Nissen < kai.nissen@wikimedia.de >
 */
class ApplicantAddress {
	use FreezableValueObject;

	private $streetAddress = '';
	private $postalCode = '';
	private $city = '';
	private $countryCode = '';

	public function getStreetAddress(): string {
		return $this->streetAddress;
	}

	public function setStreetAddress( string $streetAddress ): void {
		$this->assertIsWritable();
		$this->streetAddress = $streetAddress;
	}

	public function getPostalCode(): string {
		return $this->postalCode;
	}

	public function setPostalCode( string $postalCode ): void {
		$this->assertIsWritable();
		$this->postalCode = $postalCode;
	}

	public function getCity(): string {
		return $this->city;
	}

	public function setCity( string $city ): void {
		$this->assertIsWritable();
		$this->city = $city;
	}

	public function getCountryCode(): string {
		return $this->countryCode;
	}

	public function setCountryCode( string $countryCode ): void {
		$this->assertIsWritable();
		$this->countryCode = $countryCode;
	}

}
