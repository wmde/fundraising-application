<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\Integration\Validation;

use WMDE\Fundraising\Frontend\Domain\NullDomainNameValidator;
use WMDE\Fundraising\Frontend\Tests\Unit\Validation\ValidatorTestCase;
use WMDE\Fundraising\Frontend\UseCases\GetInTouch\GetInTouchRequest;
use WMDE\Fundraising\Frontend\Validation\GetInTouchValidator;
use WMDE\Fundraising\Frontend\Validation\MailValidator;

/**
 * @licence GNU GPL v2+
 * @author Kai Nissen < kai.nissen@wikimedia.de >
 */
class GetInTouchValidatorTest extends ValidatorTestCase {

	public function testNameFieldsAreOptional() {
		$mailValidator = new MailValidator( new NullDomainNameValidator() );
		$validator = new GetInTouchValidator( $mailValidator );
		$request = new GetInTouchRequest(
			'',
			'',
			'kh@meyer.net',
			'Hello there!',
			'I just wanted to say "Hi".'
		);
		$this->assertTrue( $validator->validate( $request )->isSuccessful() );
	}

	public function testEmailAddressIsValidated() {
		$mailValidator = new MailValidator( new NullDomainNameValidator() );
		$validator = new GetInTouchValidator( $mailValidator );
		$request = new GetInTouchRequest(
			'',
			'',
			'kh@meyer',
			'Hello there!',
			'I just wanted to say "Hi".'
		);
		$this->assertFalse( $validator->validate( $request )->isSuccessful() );
		$this->assertConstraintWasViolated( $validator->validate( $request ), 'email' );
	}

	public function testSubjectIsValidated() {
		$mailValidator = new MailValidator( new NullDomainNameValidator() );
		$validator = new GetInTouchValidator( $mailValidator );
		$request = new GetInTouchRequest(
			'',
			'',
			'kh@net.meyer',
			'',
			'I just wanted to say "Hi".'
		);
		$this->assertFalse( $validator->validate( $request )->isSuccessful() );
		$this->assertConstraintWasViolated( $validator->validate( $request ), 'subject' );
	}

	public function testMessageBodyIsValidated() {
		$mailValidator = new MailValidator( new NullDomainNameValidator() );
		$validator = new GetInTouchValidator( $mailValidator );
		$request = new GetInTouchRequest(
			'',
			'',
			'kh@net.meyer',
			'Hello there!',
			''
		);
		$this->assertFalse( $validator->validate( $request )->isSuccessful() );
		$this->assertConstraintWasViolated( $validator->validate( $request ), 'messageBody' );
	}

}
