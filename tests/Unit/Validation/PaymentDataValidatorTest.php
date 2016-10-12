<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\Unit\Validation;

use WMDE\Euro\Euro;
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

	const MIN_DONATION_AMOUNT = 1;
	const MAX_DONATION_AMOUNT = 100000;

	public function testGivenAmountWithinLimits_validationSucceeds() {
		$validator = $this->newPaymentValidator();
		$this->assertTrue( $validator->validate( 50, PaymentType::BANK_TRANSFER )->isSuccessful() );
	}

	public function testGivenAmountTooLow_validationFails() {
		$validator = $this->newPaymentValidator();
		$this->assertFalse( $validator->validate( 0.2, PaymentType::BANK_TRANSFER )->isSuccessful() );
	}

	public function testGivenAmountTooHigh_validationFails() {
		$validator = $this->newPaymentValidator();
		$this->assertFalse( $validator->validate( 100000, PaymentType::BANK_TRANSFER )->isSuccessful() );
	}

	public function testGivenAmountIsNotANumber_validationFails() {
		$validator = $this->newPaymentValidator();
		$this->assertFalse( $validator->validate( 'much money', PaymentType::BANK_TRANSFER )->isSuccessful() );
	}

	public function testGivenPaymentTypeSpecificLimits_differentPaymentTypeUsesMainLimit() {
		$validator = new PaymentDataValidator( 1, 100000, [ PaymentType::DIRECT_DEBIT => 100, PaymentType::PAYPAL => 200 ] );
		$this->assertTrue( $validator->validate( 50, PaymentType::BANK_TRANSFER )->isSuccessful() );
	}

	public function testGivenPaymentWithTypeSpecificLimits_specificLimitIsUsed() {
		$validator = new PaymentDataValidator( 10, 100000, [ PaymentType::DIRECT_DEBIT => 50, PaymentType::BANK_TRANSFER => 100 ] );

		$this->assertTrue( $validator->validate( 60, PaymentType::DIRECT_DEBIT )->isSuccessful() );
		$this->assertFalse( $validator->validate( 40, PaymentType::DIRECT_DEBIT )->isSuccessful() );
	}

	public function testNumberEqualToBoundIsAllowed() {
		$validator = $this->newPaymentValidator();
		$this->assertTrue( $validator->validate( 1, PaymentType::BANK_TRANSFER )->isSuccessful() );
	}

	public function testStringNotationBelowLowerBoundIsNotAllowed() {
		$validator = $this->newPaymentValidator();
		$this->assertFalse( $validator->validate( '0.1', PaymentType::BANK_TRANSFER )->isSuccessful() );
	}

	public function testStringNotationAboveLowerBoundIsAllowed() {
		$validator = $this->newPaymentValidator();
		$this->assertTrue( $validator->validate( '1.1', PaymentType::BANK_TRANSFER )->isSuccessful() );
	}

	public function testNumberEqualToUpperBoundIsNotAllowed() {
		$validator = $this->newPaymentValidator();
		$this->assertFalse( $validator->validate( 100000, PaymentType::BANK_TRANSFER )->isSuccessful() );
	}

	public function testStringNotationAboveUpperBoundIsNotAllowed() {
		$validator = $this->newPaymentValidator();
		$this->assertFalse( $validator->validate( '123456.78', PaymentType::BANK_TRANSFER )->isSuccessful() );
	}

	public function testStringNotationBelowUpperBoundIsAllowed() {
		$validator = $this->newPaymentValidator();
		$this->assertTrue( $validator->validate( '99999.99', PaymentType::BANK_TRANSFER )->isSuccessful() );
	}

	public function testBinaryNotationIsNotAllowed() {
		$validator = $this->newPaymentValidator();
		$this->assertFalse( $validator->validate( '0b10100111001', PaymentType::BANK_TRANSFER )->isSuccessful() );
	}

	public function testUnknownPaymentMethodsAreNotAllowed() {
		$validator = $this->newPaymentValidator();
		$this->assertFalse( $validator->validate( 99, 'DOGE' )->isSuccessful() );
	}

	private function newPaymentValidator(): PaymentDataValidator {
		return new PaymentDataValidator( self::MIN_DONATION_AMOUNT, self::MAX_DONATION_AMOUNT );
	}

	public function testGivenEuroAmountWithinLimits_validationSucceeds() {
		$this->assertTrue(
			$this->newPaymentValidator()->validate(
				Euro::newFromInt( 50 ),
				PaymentType::BANK_TRANSFER
			)->isSuccessful()
		);
	}

}
