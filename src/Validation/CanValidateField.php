<?php


namespace WMDE\Fundraising\Frontend\Validation;

trait CanValidateField {

	private function validateField( ScalarValueValidator $validator, $fieldValue, string $fieldName ) {
		if ( $validator->validate( $fieldValue) ) {
			return null;
		}

		$violation = $validator->getLastViolation();
		$violation->setSource( $fieldName );

		return $violation;
	}

}