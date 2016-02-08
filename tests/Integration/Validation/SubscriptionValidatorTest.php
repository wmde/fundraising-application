<?php


namespace WMDE\Fundraising\Frontend\Tests\Integration\Validation;

use WMDE\Fundraising\Entities\Address;
use WMDE\Fundraising\Entities\Subscription;
use WMDE\Fundraising\Frontend\Domain\NullDomainNameValidator;
use WMDE\Fundraising\Frontend\Validation\MailValidator;
use WMDE\Fundraising\Frontend\Validation\SubscriptionValidator;
use WMDE\Fundraising\Frontend\Validation\TextPolicyValidator;

class SubscriptionValidatorTest extends ValidatorTestCase {

	private function getMockTextPolicyValidator() {
		$mock = $this->getMock( TextPolicyValidator::class );
		$mock->method( 'hasHarmlessContent' )
			->willReturn( true );
		return $mock;
	}

	private function createAddress( string $saluation, string $firstName, string $lastName ): Address {
		$address = new Address();
		$address->setSalutation( $saluation );
		$address->setFirstName( $firstName );
		$address->setLastName( $lastName );
		$address->setCompany( '' );
		$address->setAddress( '' );
		$address->setCity( '' );
		$address->setPostcode( '' );
		return $address;
	}

	public function testEmailIsValidated() {
		$mailValidator = new MailValidator( new NullDomainNameValidator() );
		$subscriptionValidator = new SubscriptionValidator( $mailValidator, $this->getMockTextPolicyValidator() );
		$subscription = new Subscription();
		$subscription->setAddress( $this->createAddress( 'Herr', 'Nyan', 'Cat' ) );
		$subscription->setEmail( 'this is not a mail addess' );
		$this->assertFalse( $subscriptionValidator->validate( $subscription ) );
		$this->assertConstraintWasViolated( $subscriptionValidator->getConstraintViolations(), 'email' );
	}

	public function testFirstNameIsValidated() {
		$mailValidator = new MailValidator( new NullDomainNameValidator() );
		$subscriptionValidator = new SubscriptionValidator( $mailValidator, $this->getMockTextPolicyValidator() );
		$subscription = new Subscription();
		$subscription->setAddress( $this->createAddress( 'Herr', '', 'Cat' ) );
		$subscription->setEmail( 'nyan@meow.com' );
		$this->assertFalse( $subscriptionValidator->validate( $subscription ) );
		$this->assertConstraintWasViolated( $subscriptionValidator->getConstraintViolations(), 'firstName' );
	}

	public function testLastNameIsValidated() {
		$mailValidator = new MailValidator( new NullDomainNameValidator() );
		$subscriptionValidator = new SubscriptionValidator( $mailValidator, $this->getMockTextPolicyValidator() );
		$subscription = new Subscription();
		$subscription->setAddress( $this->createAddress( 'Herr', 'Nyan', '' ) );
		$subscription->setEmail( 'nyan@meow.com' );
		$this->assertFalse( $subscriptionValidator->validate( $subscription ) );
		$this->assertConstraintWasViolated( $subscriptionValidator->getConstraintViolations(), 'lastName' );
	}

	public function testSalutationIsValidated() {
		$mailValidator = new MailValidator( new NullDomainNameValidator() );
		$subscriptionValidator = new SubscriptionValidator( $mailValidator, $this->getMockTextPolicyValidator() );
		$subscription = new Subscription();
		$subscription->setAddress( $this->createAddress( '', 'Nyan', 'Cat' ) );
		$subscription->setEmail( 'nyan@meow.com' );
		$this->assertFalse( $subscriptionValidator->validate( $subscription ) );
		$this->assertConstraintWasViolated( $subscriptionValidator->getConstraintViolations(), 'salutation' );
	}

	public function testGivenBadWords_subscriptionIsStillValid() {
		$mailValidator = new MailValidator( new NullDomainNameValidator() );
		$policyValidator = $this->getMock( TextPolicyValidator::class );
		$policyValidator->method( 'hasHarmlessContent' )
			->willReturn( false );
		$subscriptionValidator = new SubscriptionValidator( $mailValidator, $policyValidator );
		$subscription = new Subscription();
		$subscription->setAddress( $this->createAddress( 'Herr', 'Nyan', 'Cat' ) );
		$subscription->setEmail( 'nyan@meow.com' );
		$this->assertTrue( $subscriptionValidator->validate( $subscription ) );
		$this->assertSame( [], $subscriptionValidator->getConstraintViolations() );
	}

	public function testGivenBadWords_needsModerationIsTrue() {
		$mailValidator = new MailValidator( new NullDomainNameValidator() );
		$policyValidator = $this->getMock( TextPolicyValidator::class );
		$policyValidator->method( 'hasHarmlessContent' )
			->willReturn( false );
		$subscriptionValidator = new SubscriptionValidator( $mailValidator, $policyValidator );
		$subscription = new Subscription();
		$subscription->setAddress( $this->createAddress( 'Herr', 'Nyan', 'Cat' ) );
		$subscription->setEmail( 'nyan@meow.com' );
		$this->assertTrue( $subscriptionValidator->needsModeration( $subscription ) );
	}
}
