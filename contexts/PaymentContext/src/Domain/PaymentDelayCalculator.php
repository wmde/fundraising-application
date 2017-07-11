<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\PaymentContext\Domain;

/**
 * Adds days to a given base date.
 *
 * @licence GNU GPL v2+
 * @author Kai Nissen < kai.nissen@wikimedia.de >
 */
interface PaymentDelayCalculator {

	public function calculateFirstPaymentDate(): \DateTime;

}
