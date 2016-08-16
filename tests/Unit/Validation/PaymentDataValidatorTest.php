<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Tests\Unit;

use WMDE\Fundraising\Frontend\PaymentContext\Domain\Model\PaymentType;
use WMDE\Fundraising\Frontend\Validation\PaymentDataValidator;

/**
 * @covers WMDE\Fundraising\Frontend\Validation\PaymentDataValidator
 *
 * @licence GNU GPL v2+
 * @author Kai Nissen < kai.nissen@wikimedia.de >
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class PaymentDataValidatorTest extends \PHPUnit_Framework_TestCase {

	public function testGivenAmountWithinLimits_validationSucceeds() {
		$validator = new PaymentDataValidator( 1 );
		$this->assertTrue( $validator->validate( 50, PaymentType::BANK_TRANSFER )->isSuccessful() );
	}

	public function testGivenAmountTooLow_validationFails() {
		$validator = new PaymentDataValidator( 1 );
		$this->assertFalse( $validator->validate( 0.2, PaymentType::BANK_TRANSFER )->isSuccessful() );
	}

	public function testGivenAmountIsNotANumber_validationFails() {
		$validator = new PaymentDataValidator( 1 );
		$this->assertFalse( $validator->validate( 'much money', PaymentType::BANK_TRANSFER )->isSuccessful() );
	}

	public function testGivenPaymentTypeSpecificLimits_differentPaymentTypeUsesMainLimit() {
		$validator = new PaymentDataValidator( 1, [ PaymentType::DIRECT_DEBIT => 100, PaymentType::PAYPAL => 200 ] );
		$this->assertTrue( $validator->validate( 50, PaymentType::BANK_TRANSFER )->isSuccessful() );
	}

	public function testGivenPaymentWithTypeSpecificLimits_specificLimitIsUsed() {
		$validator = new PaymentDataValidator( 10, [ PaymentType::DIRECT_DEBIT => 50, PaymentType::BANK_TRANSFER => 100 ] );

		$this->assertTrue( $validator->validate( 60, PaymentType::DIRECT_DEBIT )->isSuccessful() );
		$this->assertFalse( $validator->validate( 40, PaymentType::DIRECT_DEBIT )->isSuccessful() );
	}

	public function testNumberEqualToBoundIsAllowed() {
		$validator = new PaymentDataValidator( 1 );
		$this->assertTrue( $validator->validate( 1, PaymentType::BANK_TRANSFER )->isSuccessful() );
	}

	public function testStringNotationBelowLowerBoundIsNotAllowed() {
		$validator = new PaymentDataValidator( 1 );
		$this->assertFalse( $validator->validate( '0.1', PaymentType::BANK_TRANSFER )->isSuccessful() );
	}

	public function testStringNotationAboveLowerBoundIsAllowed() {
		$validator = new PaymentDataValidator( 1 );
		$this->assertTrue( $validator->validate( '1.1', PaymentType::BANK_TRANSFER )->isSuccessful() );
	}

	public function testBinaryNotationIsNotAllowed() {
		$validator = new PaymentDataValidator( 1 );
		$this->assertFalse( $validator->validate( '0b10100111001', PaymentType::BANK_TRANSFER )->isSuccessful() );
	}

	public function testUnknownPaymentMethodsAreNotAllowed() {
		$validator = new PaymentDataValidator( 1 );
		$this->assertFalse( $validator->validate( 99, 'DOGE' )->isSuccessful() );
	}

}
