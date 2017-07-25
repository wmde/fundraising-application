<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\Fixtures;

use WMDE\Fundraising\Frontend\PaymentContext\Domain\PaymentDelayCalculator;

/**
 * @licence GNU GPL v2+
 * @author Kai Nissen < kai.nissen@wikimedia.de >
 */
class FixedPaymentDelayCalculator implements PaymentDelayCalculator {

	private $fixedDate;
	private $fixedDelayInDays;

	public function __construct( \DateTime $fixedDate, int $fixedDelayInDays ) {
		$this->fixedDate = $fixedDate;
		$this->fixedDelayInDays = $fixedDelayInDays;
	}

	public function calculateFirstPaymentDate(): \DateTime {
		return $this->fixedDate;
	}

	public function getPaymentDelayInDays(): int {
		return $this->fixedDelayInDays;
	}

}
