<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\Fixtures;

use WMDE\Fundraising\PaymentContext\Domain\PaymentDelayCalculator;

/**
 * @licence GNU GPL v2+
 * @author Kai Nissen < kai.nissen@wikimedia.de >
 */
class FixedPaymentDelayCalculator implements PaymentDelayCalculator {

	private $fixedDate;

	public function __construct( \DateTime $fixedDate ) {
		$this->fixedDate = $fixedDate;
	}

	public function calculateFirstPaymentDate(): \DateTime {
		return $this->fixedDate;
	}

}
