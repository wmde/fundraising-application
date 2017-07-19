<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\PaymentContext\Tests\Unit\Domain;

use DateTime;
use WMDE\Fundraising\Frontend\PaymentContext\Domain\DefaultPaymentDelayCalculator;

/**
 * @covers \WMDE\Fundraising\Frontend\PaymentContext\Domain\DefaultPaymentDelayCalculator
 *
 * @licence GNU GPL v2+
 * @author Kai Nissen < kai.nissen@wikimedia.de >
 */
class DefaultPaymentDelayCalculatorTest extends \PHPUnit\Framework\TestCase {

	const PAYMENT_DELAY_IN_DAYS = 45;

	public function testCalculatorAddsIntervalToGivenDate(): void {
		$calculator = new DefaultPaymentDelayCalculator( self::PAYMENT_DELAY_IN_DAYS );
		$this->assertEquals( '2013-02-03', $calculator->calculateFirstPaymentDate( '2012-12-20' )->format( 'Y-m-d' ) );
	}

	public function testGivenNoBaseDate_calculatorUsesCurrentDate(): void {
		$calculator = new DefaultPaymentDelayCalculator( self::PAYMENT_DELAY_IN_DAYS );
		$this->assertEquals(
			self::PAYMENT_DELAY_IN_DAYS,
			( new DateTime() )->diff( $calculator->calculateFirstPaymentDate() )->days
		);
	}

}
