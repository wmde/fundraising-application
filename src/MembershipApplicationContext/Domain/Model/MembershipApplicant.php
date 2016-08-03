<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\MembershipApplicationContext\Domain\Model;

use WMDE\Fundraising\Frontend\Domain\Model\PhysicalAddress;

/**
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class MembershipApplicant {

	private $personName;
	private $physicalAddress;
	private $email;
	private $phone;
	private $dateOfBirth;

	public function __construct( ApplicantName $name, PhysicalAddress $address, EmailAddress $email,
		PhoneNumber $phone, \DateTime $dateOfBirth = null ) {

		$this->personName = $name;
		$this->physicalAddress = $address;
		$this->email = $email;
		$this->phone = $phone;
		$this->dateOfBirth = $dateOfBirth;
	}

	public function getName(): ApplicantName {
		return $this->personName;
	}

	public function getPhysicalAddress(): PhysicalAddress {
		return $this->physicalAddress;
	}

	public function getEmailAddress(): EmailAddress {
		return $this->email;
	}

	/**
	 * @return \DateTime|null
	 */
	public function getDateOfBirth() {
		return $this->dateOfBirth;
	}

	public function changeEmailAddress( EmailAddress $email ) {
		$this->email = $email;
	}

	public function getPhoneNumber(): PhoneNumber {
		return $this->phone;
	}

}
