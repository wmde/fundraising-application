<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\Unit\Validation;

use WMDE\Fundraising\Frontend\Validation\FieldTextPolicyValidator;
use WMDE\FunValidators\ConstraintViolation;
use WMDE\FunValidators\Validators\TextPolicyValidator;

/**
 * @covers \WMDE\Fundraising\Frontend\Validation\FieldTextPolicyValidator
 *
 * @licence GNU GPL v2+
 * @author Gabriel Birke < gabriel.birke@wikimedia.de >
 */
class FieldTextPolicyValidatorTest extends \PHPUnit\Framework\TestCase {

	public function testGivenHarmlessText_itSucceeds(): void {
		$textPolicy = $this->createMock( TextPolicyValidator::class );
		$textPolicy->method( $this->anything() )->willReturn( true );
		$validator = new FieldTextPolicyValidator( $textPolicy );
		$this->assertTrue( $validator->validate( 'tiny cat' )->isSuccessful() );
	}

	public function testGivenHarmfulText_itFails(): void {
		$textPolicy = $this->createMock( TextPolicyValidator::class );
		$textPolicy->method( $this->anything() )->willReturn( false );
		$validator = new FieldTextPolicyValidator( $textPolicy );
		$this->assertFalse( $validator->validate( 'mean tiger' )->isSuccessful() );
	}

	public function testGivenHarmfulText_itProvidesAConstraintViolation(): void {
		$textPolicy = $this->createMock( TextPolicyValidator::class );
		$textPolicy->method( $this->anything() )->willReturn( false );
		$validator = new FieldTextPolicyValidator( $textPolicy );

		$this->assertInstanceOf(
			ConstraintViolation::class,
			$validator->validate( 'mean tiger' )->getViolations()[0]
		);
	}
}
