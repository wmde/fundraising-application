<?php

declare(strict_types = 1);

namespace WMDE\Fundraising\Frontend\Validation;

/**
 * @licence GNU GPL v2+
 * @author Kai Nissen < kai.nissen@wikimedia.de >
 */
class AmountValidator {

	private $minAmount;

	public function __construct( int $minAmount ) {
		$this->minAmount = $minAmount;
	}

	public function validate( $amount ): ValidationResult {
		if ( !is_numeric( $amount ) ) {
			return new ValidationResult( new ConstraintViolation( $amount, 'Amount or interval is not numeric' ) );
		}

		if ( $amount < $this->minAmount ) {
			return new ValidationResult( new ConstraintViolation( $amount, 'Amount too low' ) );
		}

		return new ValidationResult();
	}

}
