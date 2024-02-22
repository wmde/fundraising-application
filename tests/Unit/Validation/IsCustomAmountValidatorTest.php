<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\Unit\Validation;

use PHPUnit\Framework\TestCase;
use WMDE\Euro\Euro;
use WMDE\Fundraising\Frontend\Validation\IsCustomAmountValidator;

/**
 * @covers \WMDE\Fundraising\Frontend\Validation\IsCustomAmountValidator
 */
class IsCustomAmountValidatorTest extends TestCase {

	public function testValidate(): void {
		$validator = new IsCustomAmountValidator( [ Euro::newFromCents( 500 ), Euro::newFromCents( 1500 ), Euro::newFromCents( 2500 ) ] );
		$amount = Euro::newFromCents( 500 );
		$this->assertFalse( $validator->validate( $amount ) );
	}

	public function testValidateWithZero(): void {
		$validator = new IsCustomAmountValidator( [ Euro::newFromCents( 500 ), Euro::newFromCents( 1500 ), Euro::newFromCents( 2500 ) ] );
		$amount = Euro::newFromCents( 0 );
		$this->assertFalse( $validator->validate( $amount ) );
	}

	public function testValidateWithRandomCommaAmount_returnsTrue(): void {
		$validator = new IsCustomAmountValidator( [ Euro::newFromCents( 500 ), Euro::newFromCents( 1500 ), Euro::newFromCents( 2500 ) ] );
		$amount = Euro::newFromCents( 4711 );
		$this->assertTrue( $validator->validate( $amount ) );
	}
}
