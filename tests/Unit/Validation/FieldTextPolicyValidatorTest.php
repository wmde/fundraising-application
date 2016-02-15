<?php

namespace WMDE\Fundraising\Tests\Unit;

use WMDE\Fundraising\Frontend\Validation\FieldTextPolicyValidator;
use WMDE\Fundraising\Frontend\Validation\TextPolicyValidator;
use WMDE\Fundraising\Frontend\Validation\ConstraintViolation;

/**
 * @covers WMDE\Fundraising\Frontend\Validation\FieldTextPolicyValidator
 *
 * @licence GNU GPL v2+
 * @author Gabriel Birke < gabriel.birke@wikimedia.de >
 */
class FieldTextPolicyValidatorTest extends \PHPUnit_Framework_TestCase {

	public function testGivenHarmlessText_itSucceeds(){
		$textPolicy = $this->getMock( TextPolicyValidator::class );
		$textPolicy->method( 'hasHarmlessContent' )->willReturn( true );
		$validator = new FieldTextPolicyValidator( $textPolicy, 0 );
		$this->assertTrue( $validator->validate( 'tiny cat' )->isSuccessful() );
	}

	public function testGivenHarmfulText_itFails(){
		$textPolicy = $this->getMock( TextPolicyValidator::class );
		$textPolicy->method( 'hasHarmlessContent' )->willReturn( false );
		$validator = new FieldTextPolicyValidator( $textPolicy, 0 );
		$this->assertFalse( $validator->validate( 'mean tiger' )->isSuccessful() );
	}

	public function testGivenHarmfulText_itProvidesAConstraintViolation(){
		$textPolicy = $this->getMock( TextPolicyValidator::class );
		$textPolicy->method( 'hasHarmlessContent' )->willReturn( false );
		$validator = new FieldTextPolicyValidator( $textPolicy, 0 );

		$this->assertInstanceOf(
			ConstraintViolation::class,
			$validator->validate( 'mean tiger' )->getViolations()[0]
		);
	}
}
