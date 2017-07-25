<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\PaymentContext\Domain;

use DateInterval;
use DateTime;

/**
 * @licence GNU GPL v2+
 * @author Kai Nissen < kai.nissen@wikimedia.de >
 */
class DefaultPaymentDelayCalculator implements PaymentDelayCalculator {

	private $paymentDelayInDays;

	public function __construct( int $paymentDelayInDays ) {
		$this->paymentDelayInDays = $paymentDelayInDays;
	}

	public function calculateFirstPaymentDate( string $baseDate = '' ): DateTime {
		$date = new DateTime( $baseDate );
		$date->add( new DateInterval( 'P' . $this->paymentDelayInDays . 'D' ) );
		return $date;
	}

	public function getPaymentDelayInDays(): int {
		return $this->paymentDelayInDays;
	}
}
