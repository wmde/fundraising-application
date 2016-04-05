<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Domain\Model;

/**
 * @licence GNU GPL v2+
 * @author Kai Nissen < kai.nissen@wikimedia.de >
 */
class PersonalInfo {

	private $personName;
	private $physicalAddress;
	private $emailAddress;

	public function __construct( PersonName $name, PhysicalAddress $address, string $emailAddress ) {
		$this->personName = $name;
		$this->physicalAddress = $address;
		$this->emailAddress = $emailAddress;
	}

	public function getPersonName(): PersonName {
		return $this->personName;
	}

	public function getPhysicalAddress(): PhysicalAddress {
		return $this->physicalAddress;
	}

	public function getEmailAddress(): string {
		return $this->emailAddress;
	}

}
