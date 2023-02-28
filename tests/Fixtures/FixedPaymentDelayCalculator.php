<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\Fixtures;

use WMDE\Fundraising\PaymentContext\Domain\PaymentDelayCalculator;

/**
 * @license GPL-2.0-or-later
 * @author Kai Nissen < kai.nissen@wikimedia.de >
 */
class FixedPaymentDelayCalculator implements PaymentDelayCalculator {

	public function __construct( private readonly \DateTime $fixedDate ) {
	}

	public function calculateFirstPaymentDate(): \DateTime {
		return $this->fixedDate;
	}

}
