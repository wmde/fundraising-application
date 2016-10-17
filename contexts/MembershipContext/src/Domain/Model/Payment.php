<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\MembershipContext\Domain\Model;

use WMDE\Euro\Euro;
use WMDE\Fundraising\Frontend\PaymentContext\Domain\Model\PaymentMethod;

/**
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 * @author Kai Nissen < kai.nissen@wikimedia.de >
 */
class Payment {

	private $interval;
	private $amount;
	private $paymentMethod;

	/**
	 * @param int $intervalInMonths
	 * @param Euro $amount
	 * @param PaymentMethod $paymentMethod
	 *
	 * @throws \InvalidArgumentException
	 */
	public function __construct( int $intervalInMonths, Euro $amount, PaymentMethod $paymentMethod ) {
		$this->assertIsValidInterval( $intervalInMonths );

		$this->interval = $intervalInMonths;
		$this->amount = $amount;
		$this->paymentMethod = $paymentMethod;
	}

	private function assertIsValidInterval( int $intervalInMonths ) {
		if ( !in_array( $intervalInMonths, [ 1, 3, 6, 12 ] ) ) {
			throw new \InvalidArgumentException( 'Interval needs to be 1, 3, 6 or 12' );
		}
	}

	public function getIntervalInMonths(): int {
		return $this->interval;
	}

	public function getAmount(): Euro {
		return $this->amount;
	}

	public function getPaymentMethod(): PaymentMethod {
		return $this->paymentMethod;
	}

	public function getYearlyAmount(): Euro {
		return Euro::newFromCents( $this->amount->getEuroCents() * $this->getPaymentsPerYear() );
	}

	private function getPaymentsPerYear(): int {
		return 12 / $this->interval;
	}

}
