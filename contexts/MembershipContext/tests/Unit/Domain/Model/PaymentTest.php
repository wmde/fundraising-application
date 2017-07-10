<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\Unit\MembershipContext\Domain\Model;

use WMDE\Euro\Euro;
use WMDE\Fundraising\Frontend\MembershipContext\Domain\Model\Payment;
use WMDE\Fundraising\Frontend\PaymentContext\Domain\Model\BankData;
use WMDE\Fundraising\Frontend\PaymentContext\Domain\Model\DirectDebitPayment;

/**
 * @covers WMDE\Fundraising\Frontend\MembershipContext\Domain\Model\Payment
 *
 * @license GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class PaymentTest extends \PHPUnit\Framework\TestCase {

	/**
	 * @dataProvider invalidIntervalProvider
	 */
	public function testGivenInvalidInterval_constructorThrowsException( int $invalidInterval ): void {
		$this->expectException( \InvalidArgumentException::class );
		new Payment(
			$invalidInterval,
			Euro::newFromInt( 42 ),
			new DirectDebitPayment( new BankData() )
		);
	}

	public function invalidIntervalProvider() {
		return [
			'you cant have infinity moneys' => [ 0 ],
			'time travel is also not allowed' => [ -1 ],
			'you cant pay 2.4 times per year' => [ 5 ],
			'you need to pay at least once per year' => [ 13 ],
			'you need to pay at least once per year!' => [ 24 ],
		];
	}

	public function testWhenIntervalIsTwelveMonths_yearlyPaymentIsBasePayment(): void {
		$payment = new Payment( 12, Euro::newFromInt( 42 ), new DirectDebitPayment( new BankData() ) );
		$this->assertEquals( 42, $payment->getYearlyAmount()->getEuroFloat() );
	}

	public function testWhenIntervalIsOneMonth_yearlyPaymentIsTwelveTimesBasePayment(): void {
		$payment = new Payment( 1, Euro::newFromInt( 10 ), new DirectDebitPayment( new BankData() ) );
		$this->assertEquals( 120, $payment->getYearlyAmount()->getEuroFloat() );
	}

	public function testWhenIntervalIsOneQuarter_yearlyPaymentIsFourTimesBasePayment(): void {
		$payment = new Payment( 3, Euro::newFromInt( 50 ), new DirectDebitPayment( new BankData() ) );
		$this->assertEquals( 200, $payment->getYearlyAmount()->getEuroFloat() );
	}

}
