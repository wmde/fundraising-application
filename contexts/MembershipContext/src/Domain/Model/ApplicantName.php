<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\MembershipContext\Domain\Model;

use WMDE\Fundraising\Frontend\FreezableValueObject;

/**
 * @licence GNU GPL v2+
 * @author Kai Nissen < kai.nissen@wikimedia.de >
 */
class ApplicantName {
	use FreezableValueObject;

	public const COMPANY_SALUTATION = 'Firma';

	private $companyName = '';

	private $salutation = '';
	private $title = '';
	private $firstName = '';
	private $lastName = '';

	private function __construct() {
	}

	public static function newPrivatePersonName(): self {
		return new self();
	}

	public static function newCompanyName(): self {
		$companyName = new self();
		$companyName->setSalutation( self::COMPANY_SALUTATION );
		return $companyName;
	}

	public function setCompanyName( string $companyName ) {
		$this->assertIsWritable();
		$this->companyName = $companyName;
	}

	public function getCompanyName(): string {
		return $this->companyName;
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
			$this->companyName
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
