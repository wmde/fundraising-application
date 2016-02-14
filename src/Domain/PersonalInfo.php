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

	public function getPersonName(): PersonName {
		return $this->personName;
	}

	public function setPersonName( PersonName $personName ) {
		$this->assertIsWritable();
		$this->personName = $personName;
	}

	public function getPhysicalAddress(): PhysicalAddress {
		return $this->physicalAddress;
	}

	public function setPhysicalAddress( PhysicalAddress $physicalAddress ) {
		$this->assertIsWritable();
		$this->physicalAddress = $physicalAddress;
	}

	public function getEmailAddress(): string {
		return $this->emailAddress;
	}

	public function setEmailAddress( string $emailAddress ) {
		$this->assertIsWritable();
		$this->emailAddress = $emailAddress;
	}

}
