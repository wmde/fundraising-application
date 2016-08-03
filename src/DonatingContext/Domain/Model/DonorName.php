<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\DonatingContext\Domain\Model;

use WMDE\Fundraising\Frontend\FreezableValueObject;

/**
 * @licence GNU GPL v2+
 * @author Kai Nissen < kai.nissen@wikimedia.de >
 */
class DonorName {
	use FreezableValueObject;

	// FIXME: these constants are used in request models and the getPersonType result is stuffed in the db
	const PERSON_PRIVATE = 'person';
	const PERSON_COMPANY = 'firma';
	const PERSON_ANONYMOUS = 'anonym';

	private $personType = '';

	private $companyName = '';

	private $salutation = '';
	private $title = '';
	private $firstName = '';
	private $lastName = '';

	private function __construct( string $nameType ) {
		$this->personType = $nameType;
	}

	public static function newPrivatePersonName(): self {
		return new self( self::PERSON_PRIVATE );
	}

	public static function newCompanyName(): self {
		return new self( self::PERSON_COMPANY );
	}

	public function getPersonType(): string {
		return $this->personType;
	}

	public function getCompanyName(): string {
		return $this->companyName;
	}

	public function setCompanyName( string $companyName ) {
		$this->assertIsWritable();
		$this->companyName = $companyName;
	}

	public function getSalutation(): string {
		return $this->salutation;
	}

	public function setSalutation( string $salutation ) {
		$this->assertIsWritable();
		$this->salutation = $salutation;
	}

	public function getTitle(): string {
		return $this->title;
	}

	public function setTitle( string $title ) {
		$this->assertIsWritable();
		$this->title = $title;
	}

	public function getFirstName(): string {
		return $this->firstName;
	}

	public function setFirstName( string $firstName ) {
		$this->assertIsWritable();
		$this->firstName = $firstName;
	}

	public function getLastName(): string {
		return $this->lastName;
	}

	public function setLastName( string $lastName ) {
		$this->assertIsWritable();
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
