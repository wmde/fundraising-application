<?php

declare(strict_types = 1);

namespace WMDE\Fundraising\Frontend\Validation;

/**
 * @licence GNU GPL v2+
 * @author Kai Nissen < kai.nissen@wikimedia.de >
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class AmountValidator {

	private $minAmount;

	private $minAmountPerType;

	/**
	 * @param float $minAmount
	 * @param float[] $minAmountPerType keys from the PaymentType enum
	 */
	public function __construct( float $minAmount, array $minAmountPerType = [] ) {
		$this->minAmount = $minAmount;
		$this->minAmountPerType = $minAmountPerType;
	}

	public function validate( $amount, string $paymentType ): ValidationResult {
		if ( !is_numeric( $amount ) ) {
			return new ValidationResult( new ConstraintViolation( $amount, 'Amount or interval is not numeric' ) );
		}

		if ( $amount < $this->getMinAmountFor( $paymentType ) ) {
			return new ValidationResult( new ConstraintViolation( $amount, 'Amount too low' ) );
		}

		return new ValidationResult();
	}

	private function getMinAmountFor( string $paymentMethod ): float {
		if ( array_key_exists( $paymentMethod, $this->minAmountPerType ) ) {
			return $this->minAmountPerType[$paymentMethod];
		}

		return $this->minAmount;
	}

}
