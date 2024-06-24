<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\Integration\Validation;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use WMDE\Fundraising\Frontend\Infrastructure\Validation\NullDomainNameValidator;
use WMDE\Fundraising\Frontend\UseCases\GetInTouch\GetInTouchRequest;
use WMDE\Fundraising\Frontend\Validation\GetInTouchValidator;
use WMDE\FunValidators\ConstraintViolation;
use WMDE\FunValidators\ValidationResult;
use WMDE\FunValidators\Validators\EmailValidator;

#[CoversClass( GetInTouchValidator::class )]
class GetInTouchValidatorTest extends TestCase {

	public function testNameFieldsAreOptional(): void {
		$mailValidator = new EmailValidator( new NullDomainNameValidator() );
		$validator = new GetInTouchValidator( $mailValidator );
		$request = new GetInTouchRequest(
			'',
			'',
			'kh@meyer.net',
			'123456',
			'Hello there!',
			'Change of address',
			'I just wanted to say "Hi".'
		);
		$this->assertTrue( $validator->validate( $request )->isSuccessful() );
	}

	public function testEmailAddressIsValidated(): void {
		$mailValidator = new EmailValidator( new NullDomainNameValidator() );
		$validator = new GetInTouchValidator( $mailValidator );
		$request = new GetInTouchRequest(
			'',
			'',
			'kh@meyer',
			'123456',
			'Hello there!',
			'Change of address',
			'I just wanted to say "Hi".'
		);
		$this->assertFalse( $validator->validate( $request )->isSuccessful() );
		$this->assertConstraintWasViolated( $validator->validate( $request ), 'email' );
	}

	private function assertConstraintWasViolated( ValidationResult $result, string $fieldName ): void {
		$this->assertContainsOnlyInstancesOf( ConstraintViolation::class, $result->getViolations() );
		$this->assertTrue( $result->hasViolations() );

		$violated = false;
		foreach ( $result->getViolations() as $violation ) {
			if ( $violation->getSource() === $fieldName ) {
				$violated = true;
			}
		}

		$this->assertTrue(
			$violated,
			'Failed asserting that constraint for field "' . $fieldName . '"" was violated.'
		);
	}

	public function testSubjectIsValidated(): void {
		$mailValidator = new EmailValidator( new NullDomainNameValidator() );
		$validator = new GetInTouchValidator( $mailValidator );
		$request = new GetInTouchRequest(
			'',
			'',
			'kh@net.meyer',
			'1234567',
			'',
			'Change of address',
			'I just wanted to say "Hi".'
		);
		$this->assertFalse( $validator->validate( $request )->isSuccessful() );
		$this->assertConstraintWasViolated( $validator->validate( $request ), 'subject' );
	}

	public function testMessageBodyIsValidated(): void {
		$mailValidator = new EmailValidator( new NullDomainNameValidator() );
		$validator = new GetInTouchValidator( $mailValidator );
		$request = new GetInTouchRequest(
			'',
			'',
			'kh@net.meyer',
			'123456',
			'Hello there!',
			'Change of address',
			''
		);
		$this->assertFalse( $validator->validate( $request )->isSuccessful() );
		$this->assertConstraintWasViolated( $validator->validate( $request ), 'messageBody' );
	}

}
