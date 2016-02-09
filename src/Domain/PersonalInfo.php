<?php

declare(strict_types = 1);

namespace WMDE\Fundraising\Frontend\Domain;

use WMDE\Fundraising\Frontend\FreezableValueObject;
use WMDE\Fundraising\Frontend\MailAddress;

/**
 * @licence GNU GPL v2+
 * @author Kai Nissen < kai.nissen@wikimedia.de >
 */
class PersonalInfo {
	use FreezableValueObject;

	private $personName;
	private $physicalAddress;
	private $emailAddress;

	/**
	 * @return PersonName
	 */
	public function getPersonName() {
		return $this->personName;
	}

	public function setPersonName( PersonName $personName ) {
		$this->personName = $personName;
	}

	/**
	 * @return PhysicalAddress
	 */
	public function getPhysicalAddress() {
		return $this->physicalAddress;
	}

	public function setPhysicalAddress( PhysicalAddress $physicalAddress ) {
		$this->physicalAddress = $physicalAddress;
	}

	/**
	 * @return MailAddress
	 */
	public function getEmailAddress() {
		return $this->emailAddress;
	}

	public function setEmailAddress( MailAddress $emailAddress ) {
		$this->emailAddress = $emailAddress;
	}

}
