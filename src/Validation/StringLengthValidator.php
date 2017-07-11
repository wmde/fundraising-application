<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Validation;

/**
 * @license GNU GPL v2+
 * @author Kai Nissen < kai.nissen@wikimedia.de >
 */
class StringLengthValidator {

	public function validate( $value, int $maxLength, int $minLength = 0 ): ValidationResult {	// @codingStandardsIgnoreLine
		if ( strlen( $value ) < $minLength || strlen( $value ) > $maxLength ) {
			return new ValidationResult( new ConstraintViolation( $value, 'incorrect_length' ) );
		}

		return new ValidationResult();
	}

}
