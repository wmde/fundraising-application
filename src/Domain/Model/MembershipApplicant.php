<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Domain\Model;

/**
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class MembershipApplicant {

	private $personName;
	private $physicalAddress;
	private $emailAddress;
	private $dateOfBirth;

	public function __construct( PersonName $name, PhysicalAddress $address, string $emailAddress, \DateTime $dateOfBirth ) {
		$this->personName = $name;
		$this->physicalAddress = $address;
		$this->emailAddress = $emailAddress;
		$this->dateOfBirth = $dateOfBirth;
	}

	// TODO: $applicant->getPersonName->getFirstName() is odd compared to // TODO: $applicant->getFirstName()
	public function getPersonName(): PersonName {
		return $this->personName;
	}

	public function getPhysicalAddress(): PhysicalAddress {
		return $this->physicalAddress;
	}

	public function getEmailAddress(): string {
		return $this->emailAddress;
	}

	public function getDateOfBirth(): \DateTime {
		return $this->dateOfBirth;
	}

	public function setEmailAddress( string $emailAddress ) {
		$this->emailAddress = $emailAddress;
	}

	// TODO: phone number
	// Create a PhoneNumber class?
	// Use something like https://github.com/giggsey/libphonenumber-for-php?

}
