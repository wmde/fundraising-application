<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Validation;

use WMDE\Fundraising\Frontend\Domain\Model\MembershipApplication;

/**
 * @license GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class MembershipApplicationValidator {

	public function validate( MembershipApplication $application ): ValidationResult {
		// TODO: implement
		return new ValidationResult();
	}

}