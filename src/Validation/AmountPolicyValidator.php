<?php

declare(strict_types = 1);

namespace WMDE\Fundraising\Frontend\Validation;

/**
 * @licence GNU GPL v2+
 * @author Kai Nissen < kai.nissen@wikimedia.de >
 */
class AmountPolicyValidator {

	private $maxAmount;
	private $maxAmountRecurring;
	private $maxAmountRecurringAnually;

	private $lastViolation;

	public function __construct( int $maxAmount, int $maxAmountRecurring, $maxAmountRecurringAnually ) {
		$this->maxAmount = $maxAmount;
		$this->maxAmountRecurring = $maxAmountRecurring;
		$this->maxAmountRecurringAnually = $maxAmountRecurringAnually;
	}

	public function validate( $amount, $interval = 0 ): bool {
		$violations = [];

		if ( $this->isOneTimeAmountTooHigh( $amount, $interval ) ||
			$this->isRecurringAmountTooHigh( $amount, $interval ) ||
			$this->isAnuallyRecurringAmountTooHigh( $amount, $interval ) ) {

			$violations[] = new ConstraintViolation( $amount, 'Amount too high' );
		}

		return empty( $violations );
	}

	public function getLastViolation(): ConstraintViolation {
		return $this->lastViolation;
	}

	private function isOneTimeAmountTooHigh( $amount, $interval ) {
		if ( $interval === 0 ) {
			return $amount >= $this->maxAmount;
		}
		return false;
	}

	private function isRecurringAmountTooHigh( $amount, $interval ) {
		if ( $interval > 0 && $interval < 12 ) {
			return $amount >= $this->maxAmountRecurring;
		}
		return false;
	}

	private function isAnuallyRecurringAmountTooHigh( $amount, $interval ) {
		if ( $interval === 12 ) {
			return $amount >= $this->maxAmountRecurringAnually;
		}
		return false;
	}

}
