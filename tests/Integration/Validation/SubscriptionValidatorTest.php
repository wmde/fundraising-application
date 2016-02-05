<?php


namespace WMDE\Fundraising\Frontend\Tests\Integration\Validation;

use WMDE\Fundraising\Entities\Address;
use WMDE\Fundraising\Entities\Subscription;
use WMDE\Fundraising\Frontend\Domain\NullDomainNameValidator;
use WMDE\Fundraising\Frontend\Validation\MailValidator;
use WMDE\Fundraising\Frontend\Validation\SubscriptionValidator;

class SubscriptionValidatorTest extends ValidatorTestCase {

	public function testEmailIsValidated() {
		$mailValidator = new MailValidator( new NullDomainNameValidator() );
		$subscriptionValidator = new SubscriptionValidator( $mailValidator );
		$subscription = new Subscription();
		$address = new Address();
		$address->setSalutation( 'Herr' );
		$address->setFirstName( 'Nyan' );
		$address->setLastName( 'Cat' );
		$subscription->setAddress( $address );
		$subscription->setEmail( 'this is not a mail addess' );
		$this->assertFalse( $subscriptionValidator->validate( $subscription ) );
		$this->assertConstraintWasViolated( $subscriptionValidator->getConstraintViolations(), 'email' );
	}

	public function testFirstNameIsValidated() {
		$mailValidator = new MailValidator( new NullDomainNameValidator() );
		$subscriptionValidator = new SubscriptionValidator( $mailValidator );
		$subscription = new Subscription();
		$address = new Address();
		$address->setSalutation( 'Herr' );
		$address->setFirstName( '' );
		$address->setLastName( 'Cat' );
		$subscription->setAddress( $address );
		$subscription->setEmail( 'nyan@meow.com' );
		$this->assertFalse( $subscriptionValidator->validate( $subscription ) );
		$this->assertConstraintWasViolated( $subscriptionValidator->getConstraintViolations(), 'firstName' );
	}

	public function testLastNameIsValidated() {
		$mailValidator = new MailValidator( new NullDomainNameValidator() );
		$subscriptionValidator = new SubscriptionValidator( $mailValidator );
		$subscription = new Subscription();
		$address = new Address();
		$address->setSalutation( 'Herr' );
		$address->setFirstName( 'Nyan' );
		$address->setLastName( '' );
		$subscription->setAddress( $address );
		$subscription->setEmail( 'nyan@meow.com' );
		$this->assertFalse( $subscriptionValidator->validate( $subscription ) );
		$this->assertConstraintWasViolated( $subscriptionValidator->getConstraintViolations(), 'lastName' );
	}

	public function testSalutationIsValidated() {
		$mailValidator = new MailValidator( new NullDomainNameValidator() );
		$subscriptionValidator = new SubscriptionValidator( $mailValidator );
		$subscription = new Subscription();
		$address = new Address();
		$address->setSalutation( '' );
		$address->setFirstName( 'Nyan' );
		$address->setLastName( 'Cat' );
		$subscription->setAddress( $address );
		$subscription->setEmail( 'nyan@meow.com' );
		$this->assertFalse( $subscriptionValidator->validate( $subscription ) );
		$this->assertConstraintWasViolated( $subscriptionValidator->getConstraintViolations(), 'salutation' );
	}

}
