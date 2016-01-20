<?php


namespace WMDE\Fundraising\Frontend\Validation;

trait CanValidateField {

	private function validateField( ScalarValueValidator $validator, $fieldValue, string $fieldName ) {
		if ( !$validator->validate( $fieldValue) ) {
			$violation = $validator->getLastViolation();
			$violation->setSource( $fieldName );
			return $violation;
		}
		return null;
	}

}