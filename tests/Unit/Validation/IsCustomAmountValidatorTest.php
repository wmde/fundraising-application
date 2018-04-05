<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\Unit\Validation;

use WMDE\Euro\Euro;
use WMDE\Fundraising\Frontend\Validation\IsCustomAmountValidator;
use PHPUnit\Framework\TestCase;

class IsCustomAmountValidatorTest extends TestCase {

	public function testValidate() {
		$validator = new IsCustomAmountValidator( [ Euro::newFromCents( 500 ), Euro::newFromCents( 1500 ), Euro::newFromCents( 2500 ) ] );
		$amount = Euro::newFromCents( 500 );
		$this->assertFalse( $validator->validate( $amount ) );
	}

	public function testValidateWithZero() {
		$validator = new IsCustomAmountValidator( [ Euro::newFromCents( 500 ), Euro::newFromCents( 1500 ), Euro::newFromCents( 2500 ) ] );
		$amount = Euro::newFromCents( 0 );
		$this->assertFalse( $validator->validate( $amount ) );
	}

	public function testValidateWithRandomCommaAmount_returnsTrue() {
		$validator = new IsCustomAmountValidator( [ Euro::newFromCents( 500 ), Euro::newFromCents( 1500 ), Euro::newFromCents( 2500 ) ] );
		$amount = Euro::newFromCents( 4711 );
		$this->assertTrue( $validator->validate( $amount ) );
	}
}
