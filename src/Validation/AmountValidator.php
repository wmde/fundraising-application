<?php

declare(strict_types = 1);

namespace WMDE\Fundraising\Frontend\Validation;

/**
 * @licence GNU GPL v2+
 * @author Kai Nissen < kai.nissen@wikimedia.de >
 */
class AmountValidator implements ScalarValueValidator {

	private $minAmount;

	private $lastViolation;

	public function __construct( int $minAmount ) {
		$this->minAmount = $minAmount;
	}

	public function validate( $amount ): bool {
		if ( !is_numeric( $amount ) ) {
			$this->lastViolation = new ConstraintViolation( $amount, 'Amount or interval is not numeric' );
			return false;
		}

		if ( $amount < $this->minAmount ) {
			$this->lastViolation = new ConstraintViolation( $amount, 'Amount too low' );
			return false;
		}

		return true;
	}

	public function getLastViolation(): ConstraintViolation {
		return $this->lastViolation;
	}

}
