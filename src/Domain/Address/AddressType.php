<?php

namespace WMDE\Fundraising\Frontend\Domain\Address;

/**
 * @license GNU GPL v2+
 * @author Kai Nissen < kai.nissen@wikimedia.de >
 */
interface AddressType {

	const ADDRESS_TYPE_COMPANY = 'firma';
	const ADDRESS_TYPE_PERSON = 'person';
	const ADDRESS_TYPE_ANONYMOUS = 'anonym';

	public function getAddressType();

	public function toArray();

}
