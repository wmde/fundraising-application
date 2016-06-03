<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Validation;

/**
 * @license GNU GPL v2+
 * @author Gabriel Birke < gabriel.birke@wikimedia.de >
 */
class RequiredFieldValidator {

	public function validate( $value ): ValidationResult {
		if ( $value === '' ) {
			return new ValidationResult( new ConstraintViolation( $value, 'field_required' ) );
		}

		return new ValidationResult();
	}

}