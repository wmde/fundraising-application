<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\Fixtures;

use WMDE\Fundraising\Frontend\Validation\StringLengthValidator;
use WMDE\Fundraising\Frontend\Validation\ValidationResult;

/**
 * @license GNU GPL v2+
 * @author Gabriel Birke < gabriel.birke@wikimedia.de >
 */
class SucceedingStringLengthValidator extends StringLengthValidator {

	public function __construct() {
	}

	public function validate( $string ): ValidationResult {
		return new ValidationResult();
	}
}