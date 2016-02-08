<?php

namespace WMDE\Fundraising\Frontend\Domain\Address;

use WMDE\Fundraising\Frontend\Domain\Address;

/**
 * @license GNU GPL v2+
 * @author Kai Nissen < kai.nissen@wikimedia.de >
 */
class CompanyAddress extends Address implements AddressType {

	private $companyName = '';

	public function getAddressType() {
		return self::ADDRESS_TYPE_COMPANY;
	}

	public function toArray() {
		return array_filter( get_object_vars( $this ) );
	}

	public function getCompanyName(): string {
		return $this->companyName;
	}

	public function setCompanyName( string $companyName ) {
		$this->companyName = $companyName;

		return $this;
	}

}
