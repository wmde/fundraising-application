<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Validation;

/**
 * @licence GNU GPL v2+
 * @author Kai Nissen < kai.nissen@wikimedia.de >
 */
class AmountPolicyValidator {

	private $maxAmount;
	private $maxAmountRecurring;
	private $maxAmountRecurringAnnually;

	public function __construct( int $maxAmount, int $maxAmountRecurring, $maxAmountRecurringAnnually ) {
		$this->maxAmount = $maxAmount;
		$this->maxAmountRecurring = $maxAmountRecurring;
		$this->maxAmountRecurringAnnually = $maxAmountRecurringAnnually;
	}

	public function validate( float $amount, int $interval ): ValidationResult {
		if ( $this->isOneTimeAmountTooHigh( $amount, $interval ) ||
			$this->isRecurringAmountTooHigh( $amount, $interval ) ||
			$this->isAnuallyRecurringAmountTooHigh( $amount, $interval ) ) {

			return new ValidationResult( new ConstraintViolation( $amount, 'This field is required' ) );
		}

		return new ValidationResult();
	}

	private function isOneTimeAmountTooHigh( float $amount, int $interval ): bool {
		if ( $interval === 0 ) {
			return $amount >= $this->maxAmount;
		}
		return false;
	}

	private function isRecurringAmountTooHigh( float $amount, int $interval ): bool {
		if ( $interval > 0 && $interval < 12 ) {
			return $amount >= $this->maxAmountRecurring;
		}
		return false;
	}

	private function isAnuallyRecurringAmountTooHigh( float $amount, int $interval ): bool {
		if ( $interval === 12 ) {
			return $amount >= $this->maxAmountRecurringAnnually;
		}
		return false;
	}

}
