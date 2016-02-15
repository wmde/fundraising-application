<?php


namespace WMDE\Fundraising\Frontend\Validation;

/**
 * @license GNU GPL v2+
 * @author Gabriel Birke < gabriel.birke@wikimedia.de >
 */
class RequiredFieldValidator {

	public function validate( $value ): ValidationResult {
		if ( empty( $value ) ) {
			return new ValidationResult( new ConstraintViolation( $value, 'This field is required' ) );
		}

		return new ValidationResult();
	}

}