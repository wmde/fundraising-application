<?php


namespace WMDE\Fundraising\Frontend\Tests\Integration\Validation;

use WMDE\Fundraising\Entities\Request;
use WMDE\Fundraising\Frontend\Domain\NullDomainNameValidator;
use WMDE\Fundraising\Frontend\Validation\MailValidator;
use WMDE\Fundraising\Frontend\Validation\RequestValidator;

class RequestValidatorTest extends ValidatorTestCase {

	public function testEmailIsValidated() {
		$mailValidator = new MailValidator( new NullDomainNameValidator() );
		$requestValidator = new RequestValidator( $mailValidator );
		$request = new Request();
		$request->setAnrede( 'Herr' );
		$request->setVorname( 'Nyan' );
		$request->setNachname( 'Cat' );
		$request->setEmail( 'this is not a mail addess' );
		$this->assertFalse( $requestValidator->validate( $request ) );
		$this->assertConstraintWasViolated( $requestValidator->getConstraintViolations(), 'email' );
	}

	public function testFirstNameIsValidated() {
		$mailValidator = new MailValidator( new NullDomainNameValidator() );
		$requestValidator = new RequestValidator( $mailValidator );
		$request = new Request();
		$request->setAnrede( 'Herr' );
		$request->setVorname( '' );
		$request->setNachname( 'Cat' );
		$request->setEmail( 'nyan@meow.com' );
		$this->assertFalse( $requestValidator->validate( $request ) );
		$this->assertConstraintWasViolated( $requestValidator->getConstraintViolations(), 'vorname' );
	}

	public function testLastNameIsValidated() {
		$mailValidator = new MailValidator( new NullDomainNameValidator() );
		$requestValidator = new RequestValidator( $mailValidator );
		$request = new Request();
		$request->setAnrede( 'Herr' );
		$request->setVorname( 'Nyan' );
		$request->setNachname( '' );
		$request->setEmail( 'nyan@meow.com' );
		$this->assertFalse( $requestValidator->validate( $request ) );
		$this->assertConstraintWasViolated( $requestValidator->getConstraintViolations(), 'nachname' );
	}

	public function testSalutationNameIsValidated() {
		$mailValidator = new MailValidator( new NullDomainNameValidator() );
		$requestValidator = new RequestValidator( $mailValidator );
		$request = new Request();
		$request->setAnrede( '' );
		$request->setVorname( 'Nyan' );
		$request->setNachname( 'Cat' );
		$request->setEmail( 'nyan@meow.com' );
		$this->assertFalse( $requestValidator->validate( $request ) );
		$this->assertConstraintWasViolated( $requestValidator->getConstraintViolations(), 'anrede' );
	}

}
