<?php


namespace WMDE\Fundraising\Frontend\Tests\Unit\Validation;

use WMDE\Fundraising\Frontend\Validation\ConstraintViolation;
use WMDE\Fundraising\Frontend\Validation\ValidationConstraint;
use WMDE\Fundraising\Frontend\Validation\ScalarValueValidator;

/**
 * @covers WMDE\Fundraising\Frontend\Validation\ValidationConstraint
 *
 * @license GNU GPL v2+
 * @author Gabriel Birke < gabriel.birke@wikimedia.de >
 */
class ValidationConstraintTest extends \PHPUnit_Framework_TestCase {
	private $validator;
	private $valueObject;

	public function setUp() {
		parent::setUp();
		$this->validator = $this->getMock( ScalarValueValidator::class );
		$this->valueObject = $this->getMock( \stdClass::class, [ 'getTest' ] );
		$this->valueObject->method( 'getTest' )->willReturn( 'nyan' );
	}

	public function testGivenAValidValue_constraintReturnsNull() {
		$this->validator->method( 'validate' )->willReturn( true );
		$constraint = new ValidationConstraint( 'test', $this->validator );
		$this->assertNull( $constraint->validate( $this->valueObject ) );
	}

	public function testGivenAnInvalidValue_constraintReturnsConstraintViolation() {

		$this->validator->method( 'validate' )->willReturn( false );
		$this->validator->method( 'getLastViolation' )->willReturn(
			new ConstraintViolation( 'nyan', 'Too many missing cats!', $this->validator )
		);
		$constraint = new ValidationConstraint( 'test', $this->validator );
		$this->assertInstanceOf( ConstraintViolation::class, $constraint->validate( $this->valueObject ) );
	}

	public function testGivenAnInvalidValue_constraintViolationSourceConatinsClassAndFieldName() {

		$this->validator->method( 'validate' )->willReturn( false );
		$this->validator->method( 'getLastViolation' )->willReturn(
			new ConstraintViolation( 'nyan', 'Too many missing cats!', $this->validator )
		);
		$constraint = new ValidationConstraint( 'test', $this->validator );
		$violation = $constraint->validate( $this->valueObject );
		$this->assertRegExp( '/^Mock_stdClass_\\w+\\.test$/', $violation->getSource() );
	}
}
