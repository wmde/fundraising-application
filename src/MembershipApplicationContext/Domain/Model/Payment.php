<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\MembershipApplicationContext\Domain\Model;

use WMDE\Euro\Euro;
use WMDE\Fundraising\Frontend\PaymentContext\Domain\Model\BankData;

/**
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class Payment {

	private $interval;
	private $amount;
	private $bankData;

	/**
	 * @param int $intervalInMonths
	 * @param Euro $amount
	 * @param BankData $bankData
	 *
	 * @throws \InvalidArgumentException
	 */
	public function __construct( int $intervalInMonths, Euro $amount, BankData $bankData ) {
		$this->assertIsValidInterval( $intervalInMonths );

		$this->interval = $intervalInMonths;
		$this->amount = $amount;
		$this->bankData = $bankData;
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

	public function getBankData(): BankData {
		return $this->bankData;
	}

	public function getYearlyAmount(): Euro {
		return Euro::newFromCents( $this->amount->getEuroCents() * $this->getPaymentsPerYear() );
	}

	private function getPaymentsPerYear(): int {
		return 12 / $this->interval;
	}

}
