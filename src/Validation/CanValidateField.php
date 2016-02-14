<?php


namespace WMDE\Fundraising\Frontend\Validation;

trait CanValidateField {

	/**
	 * @param ScalarValueValidator $validator
	 * @param mixed $fieldValue
	 * @param string $fieldName
	 *
	 * @return ConstraintViolation|null
	 */
	private function validateField( ScalarValueValidator $validator, $fieldValue, string $fieldName ) {
		if ( $validator->validate( $fieldValue) ) {
			return null;
		}

		$violation = $validator->getLastViolation();
		$violation->setSource( $fieldName );

		return $violation;
	}

}