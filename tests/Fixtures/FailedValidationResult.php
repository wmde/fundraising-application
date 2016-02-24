<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\Fixtures;

use WMDE\Fundraising\Frontend\Validation\ConstraintViolation;
use WMDE\Fundraising\Frontend\Validation\ValidationResult;

/**
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class FailedValidationResult extends ValidationResult {

	/** @noinspection PhpMissingParentConstructorInspection */
	public function __construct() {
	}

	public function isSuccessful(): bool {
		return false;
	}

	public function hasViolations(): bool {
		return true;
	}

	/**
	 * @return ConstraintViolation[]
	 */
	public function getViolations(): array {
		return [ new ConstraintViolation( '', '' ) ];
	}

}
