<?php

namespace WMDE\Fundraising\Frontend\Domain\Address;

/**
 * @license GNU GPL v2+
 * @author Kai Nissen < kai.nissen@wikimedia.de >
 */
class AnonymousAddress implements AddressType {

	public function getAddressType() {
		return self::ADDRESS_TYPE_ANONYMOUS;
	}

	public function toArray() {
		return [];
	}

}
