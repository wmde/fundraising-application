<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\Unit\Domain\Model;

use WMDE\Fundraising\Frontend\Domain\Model\BankData;
use WMDE\Fundraising\Frontend\Domain\Model\Euro;
use WMDE\Fundraising\Frontend\Domain\Model\MembershipPayment;

/**
 * @covers WMDE\Fundraising\Frontend\Domain\Model\MembershipPayment
 *
 * @license GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class MembershipPaymentTest extends \PHPUnit_Framework_TestCase {

	/**
	 * @dataProvider invalidIntervalProvider
	 */
	public function testGivenInvalidInterval_constructorThrowsException( int $invalidInterval ) {
		$this->expectException( \InvalidArgumentException::class );
		new MembershipPayment(
			$invalidInterval,
			Euro::newFromInt( 42 ),
			new BankData()
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

	public function testWhenIntervalIsTwelveMonths_yearlyPaymentIsBasePayment() {
		$payment = new MembershipPayment( 12, Euro::newFromInt( 42 ), new BankData() );
		$this->assertEquals( 42, $payment->getYearlyAmount()->getEuroFloat() );
	}

	public function testWhenIntervalIsOneMonth_yearlyPaymentIsTwelveTimesBasePayment() {
		$payment = new MembershipPayment( 1, Euro::newFromInt( 10 ), new BankData() );
		$this->assertEquals( 120, $payment->getYearlyAmount()->getEuroFloat() );
	}

	public function testWhenIntervalIsOneQuarter_yearlyPaymentIsFourTimesBasePayment() {
		$payment = new MembershipPayment( 3, Euro::newFromInt( 50 ), new BankData() );
		$this->assertEquals( 200, $payment->getYearlyAmount()->getEuroFloat() );
	}

}
