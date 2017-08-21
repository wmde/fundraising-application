<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\MembershipContext\Domain\Model;

use WMDE\Fundraising\Frontend\Infrastructure\EmailAddress;

/**
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class Applicant {

	private $personName;
	private $physicalAddress;
	private $email;
	private $phone;
	private $dateOfBirth;

	public function __construct( ApplicantName $name, ApplicantAddress $address, EmailAddress $email,
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

	public function getPhysicalAddress(): ApplicantAddress {
		return $this->physicalAddress;
	}

	public function getEmailAddress(): EmailAddress {
		return $this->email;
	}

	public function getDateOfBirth(): ?\DateTime {
		return $this->dateOfBirth;
	}

	public function changeEmailAddress( EmailAddress $email ): void {
		$this->email = $email;
	}

	public function getPhoneNumber(): PhoneNumber {
		return $this->phone;
	}

}
