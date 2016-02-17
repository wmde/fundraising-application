<?php

namespace WMDE\Fundraising\Tests\Unit;

use WMDE\Fundraising\Frontend\Domain\Model\PaymentType;
use WMDE\Fundraising\Frontend\Validation\AmountValidator;

/**
 * @covers WMDE\Fundraising\Frontend\Validation\AmountValidator
 *
 * @licence GNU GPL v2+
 * @author Kai Nissen < kai.nissen@wikimedia.de >
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class AmountValidatorTest extends \PHPUnit_Framework_TestCase {

	public function testGivenAmountWithinLimits_validationSucceeds() {
		$validator = new AmountValidator( 1 );
		$this->assertTrue( $validator->validate( 50, PaymentType::BITCOIN )->isSuccessful() );
	}

	public function testGivenAmountTooLow_validationFails() {
		$validator = new AmountValidator( 1 );
		$this->assertFalse( $validator->validate( 0.2, PaymentType::BITCOIN )->isSuccessful() );
	}

	public function testGivenAmountIsNotANumber_validationFails() {
		$validator = new AmountValidator( 1 );
		$this->assertFalse( $validator->validate( 'much money', PaymentType::BITCOIN )->isSuccessful() );
	}

	public function testGivenPaymentTypeSpecificLimits_differentPaymentTypeUsesMainLimit() {
		$validator = new AmountValidator( 1, [ PaymentType::CASH => 100, PaymentType::BANK_TRANSFER => 200 ] );
		$this->assertTrue( $validator->validate( 50, PaymentType::BITCOIN )->isSuccessful() );
	}

	public function testGivenPaymentWithTypeSpecificLimits_specificLimitIsUsed() {
		$validator = new AmountValidator( 10, [ PaymentType::CASH => 50, PaymentType::BANK_TRANSFER => 100 ] );

		$this->assertTrue( $validator->validate( 60, PaymentType::CASH )->isSuccessful() );
		$this->assertFalse( $validator->validate( 40, PaymentType::CASH )->isSuccessful() );
	}

	public function testNumberEqualToBoundIsAllowed() {
		$validator = new AmountValidator( 1 );
		$this->assertTrue( $validator->validate( 1, PaymentType::BITCOIN )->isSuccessful() );
	}

}
