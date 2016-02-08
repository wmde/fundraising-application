<?php

namespace WMDE\Fundraising\Frontend\Domain\Address;

use WMDE\Fundraising\Frontend\Domain\Address;

/**
 * @license GNU GPL v2+
 * @author Kai Nissen < kai.nissen@wikimedia.de >
 */
class PersonAddress extends Address {

	private $salutation = '';
	private $title = '';
	private $firstName = '';
	private $lastName = '';

	public function getAddressType(): string {
		return self::ADDRESS_TYPE_PERSON;
	}

	// TODO: is `toArray()` actually needed?
	public function toArray(): array {
		return array_filter( get_object_vars( $this ) );
	}

	public function getSalutation(): string {
		return $this->salutation;
	}

	public function setSalutation( string $salutation ) {
		$this->salutation = $salutation;

		return $this;
	}

	public function getTitle(): string {
		return $this->title;
	}

	public function setTitle( string $title ) {
		$this->title = $title;

		return $this;
	}

	public function getFirstName(): string {
		return $this->firstName;
	}

	public function setFirstName( string $firstName ) {
		$this->firstName = $firstName;

		return $this;
	}

	public function getLastName(): string {
		return $this->lastName;
	}

	public function setLastName( string $lastName ) {
		$this->lastName = $lastName;

		return $this;
	}

}
