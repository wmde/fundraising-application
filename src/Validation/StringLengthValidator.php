<?php

namespace WMDE\Fundraising\Frontend\Validation;

/**
 * @license GNU GPL v2+
 * @author Kai Nissen < kai.nissen@wikimedia.de >
 */
class StringLengthValidator {

	public function validate( $value, int $maxLength, int $minLength = 0 ): ValidationResult {
		if ( strlen( $value ) < $minLength || strlen( $value ) > $maxLength ) {
			return new ValidationResult( new ConstraintViolation( $value, 'Value violates length limit constraints.' ) );
		}

		return new ValidationResult();
	}

}
