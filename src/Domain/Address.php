<?php

namespace WMDE\Fundraising\Frontend\Domain;

use WMDE\Fundraising\Frontend\Domain\Address\AddressType;

/**
 * @license GNU GPL v2+
 * @author Kai Nissen < kai.nissen@wikimedia.de >
 */
abstract class Address implements AddressType {

	private $address = '';
	private $postcode = '';
	private $city = '';
	private $countryCode = '';
	private $email = '';

	public function getAddress(): string {
		return $this->address;
	}

	public function setAddress( string $address ) {
		$this->address = $address;

		return $this;
	}

	public function getPostcode(): string {
		return $this->postcode;
	}

	public function setPostalCode( string $postcode ) {
		$this->postcode = $postcode;

		return $this;
	}

	public function getCity():string {
		return $this->city;
	}

	public function setCity( string $city ) {
		$this->city = $city;

		return $this;
	}

	public function getCountryCode(): string {
		return $this->countryCode;
	}

	public function setCountryCode( string $countryCode ) {
		$this->countryCode = $countryCode;

		return $this;
	}

	public function getEmail(): string {
		return $this->email;
	}

	public function setEmail( string $email ) {
		$this->email = $email;

		return $this;
	}

}
