<?php


namespace WMDE\Fundraising\Frontend\Validation;

/**
 * @license GNU GPL v2+
 * @author Gabriel Birke < gabriel.birke@wikimedia.de >
 */
class RequiredFieldValidator implements ValueValidator {
	private $lastViolation = null;

	public function validate( $value ): bool {
		if ( !empty ( $value ) ) {
			return true;
		}
		$this->lastViolation = new ConstraintViolation( $value, 'This field is required', $this );
		return false;
	}

	public function getLastViolation(): ConstraintViolation {
		return $this->lastViolation;
	}

}