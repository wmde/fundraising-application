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
		$this->assertTrue( $validator->validate( 50, PaymentType::BANK_TRANSFER )->isSuccessful() );
	}

	public function testGivenAmountTooLow_validationFails() {
		$validator = new AmountValidator( 1 );
		$this->assertFalse( $validator->validate( 0.2, PaymentType::BANK_TRANSFER )->isSuccessful() );
	}

	public function testGivenAmountIsNotANumber_validationFails() {
		$validator = new AmountValidator( 1 );
		$this->assertFalse( $validator->validate( 'much money', PaymentType::BANK_TRANSFER )->isSuccessful() );
	}

	public function testGivenPaymentTypeSpecificLimits_differentPaymentTypeUsesMainLimit() {
		$validator = new AmountValidator( 1, [ PaymentType::DIRECT_DEBIT => 100, PaymentType::PAYPAL => 200 ] );
		$this->assertTrue( $validator->validate( 50, PaymentType::BANK_TRANSFER )->isSuccessful() );
	}

	public function testGivenPaymentWithTypeSpecificLimits_specificLimitIsUsed() {
		$validator = new AmountValidator( 10, [ PaymentType::DIRECT_DEBIT => 50, PaymentType::BANK_TRANSFER => 100 ] );

		$this->assertTrue( $validator->validate( 60, PaymentType::DIRECT_DEBIT )->isSuccessful() );
		$this->assertFalse( $validator->validate( 40, PaymentType::DIRECT_DEBIT )->isSuccessful() );
	}

	public function testNumberEqualToBoundIsAllowed() {
		$validator = new AmountValidator( 1 );
		$this->assertTrue( $validator->validate( 1, PaymentType::BANK_TRANSFER )->isSuccessful() );
	}

	public function testStringNotationBelowLowerBoundIsNotAllowed() {
		$validator = new AmountValidator( 1 );
		$this->assertFalse( $validator->validate( '0.1', PaymentType::BANK_TRANSFER )->isSuccessful() );
	}

	public function testStringNotationAboveLowerBoundIsAllowed() {
		$validator = new AmountValidator( 1 );
		$this->assertTrue( $validator->validate( '1.1', PaymentType::BANK_TRANSFER )->isSuccessful() );
	}

	public function testBinaryNotationIsNotAllowed() {
		$validator = new AmountValidator( 1 );
		$this->assertFalse( $validator->validate( '0b10100111001', PaymentType::BANK_TRANSFER )->isSuccessful() );
	}

}
