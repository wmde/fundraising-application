<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Validation;

/**
 * @license GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class ValidationResult {

	private $violations;

	public function __construct( ConstraintViolation ...$violations ) {
		$this->violations = $violations;
	}

	public function isSuccessful(): bool {
		return empty( $this->violations );
	}

	public function hasViolations(): bool {
		return !empty( $this->violations );
	}

	/**
	 * @return ConstraintViolation[]
	 */
	public function getViolations(): array {
		return $this->violations;
	}

}