<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\Fixtures;

use WMDE\FunValidators\ConstraintViolation;
use WMDE\FunValidators\ValidationResult;

class FailedValidationResult extends ValidationResult {

	public function __construct() {
		parent::__construct( new ConstraintViolation( '', '' ) );
	}
}
