<?php


namespace WMDE\Fundraising\Frontend\Validation;

/**
 * @license GNU GPL v2+
 * @author Gabriel Birke < gabriel.birke@wikimedia.de >
 */
class RequiredFieldValidator implements ScalarValueValidator {

	private $lastViolation = null;

	public function validate( $value ): bool {
		if ( empty( $value ) ) {
			$this->lastViolation = new ConstraintViolation( $value, 'This field is required' );
			return false;
		}

		return true;
	}

	public function getLastViolation(): ConstraintViolation {
		return $this->lastViolation;
	}

}