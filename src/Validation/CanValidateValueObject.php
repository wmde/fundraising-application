<?php

namespace WMDE\Fundraising\Frontend\Validation;

trait CanValidateValueObject {

	private function validateValueObject( InstanceValidator $validator, $valueObject ) {
		$validator->validate( $valueObject );
		return $validator->getConstraintViolations();
	}

}