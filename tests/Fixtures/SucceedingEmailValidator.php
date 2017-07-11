<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\Fixtures;

use WMDE\Fundraising\Frontend\Validation\EmailValidator;
use WMDE\Fundraising\Frontend\Validation\ValidationResult;

/**
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class SucceedingEmailValidator extends EmailValidator {

	public function __construct() {
	}

	public function validate( $emailAddress ): ValidationResult {	// @codingStandardsIgnoreLine
		return new ValidationResult();
	}

}
