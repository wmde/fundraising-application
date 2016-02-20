<?php

declare(strict_types = 1);

namespace WMDE\Fundraising\Frontend\Domain;

use WMDE\Fundraising\Frontend\FreezableValueObject;

/**
 * @licence GNU GPL v2+
 * @author Kai Nissen < kai.nissen@wikimedia.de >
 */
class PersonName {
	use FreezableValueObject;

	const PERSON_PRIVATE = 'person';
	const PERSON_COMPANY = 'firma';

	private $personType = '';

	private $companyName = '';

	private $salutation = '';
	private $title = '';
	private $firstName = '';
	private $lastName = '';

	private function __construct( string $nameType ) {
		$this->personType = $nameType;
	}

	public static function newPrivatePersonName() {
		return new self( self::PERSON_PRIVATE );
	}

	public static function newCompanyName() {
		return new self( self::PERSON_COMPANY );
	}

	public function getPersonType(): string {
		return $this->personType;
	}

	public function setPersonType( string $personType ) {
		$this->personType = $personType;
	}

	public function getCompanyName(): string {
		return $this->companyName;
	}

	public function setCompanyName( string $companyName ) {
		$this->companyName = $companyName;
	}

	public function getSalutation(): string {
		return $this->salutation;
	}

	public function setSalutation( string $salutation ) {
		$this->salutation = $salutation;
	}

	public function getTitle(): string {
		return $this->title;
	}

	public function setTitle( string $title ) {
		$this->title = $title;
	}

	public function getFirstName(): string {
		return $this->firstName;
	}

	public function setFirstName( string $firstName ) {
		$this->firstName = $firstName;
	}

	public function getLastName(): string {
		return $this->lastName;
	}

	public function setLastName( string $lastName ) {
		$this->lastName = $lastName;
	}

	public function getFullName(): string {
		return join( ', ', array_filter( [
			$this->getFullPrivatePersonName(),
			$this->getCompanyName()
		] ) );
	}

	private function getFullPrivatePersonName(): string {
		return join( ' ', array_filter( [
			$this->getTitle(),
			$this->getFirstName(),
			$this->getLastName()
		] ) );
	}

}
