<?php

namespace WMDE\Fundraising\Frontend\Validation;

use WMDE\Fundraising\Frontend\Domain\Address\AnonymousAddress;

/**
 * @license GNU GPL v2+
 * @author Kai Nissen < kai.nissen@wikimedia.de >
 */
class AnonymousAddressValidator extends AddressValidator {

	/**
	 * @param AnonymousAddress $instance
	 * @return bool
	 */
	public function validate( $instance ): bool {
		return true;
	}

}