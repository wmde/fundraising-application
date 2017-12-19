<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\Integration\Validation;

use WMDE\Fundraising\Entities\Address;
use WMDE\Fundraising\Entities\Subscription;
use WMDE\Fundraising\Frontend\Infrastructure\NullDomainNameValidator;
use WMDE\Fundraising\Frontend\SubscriptionContext\Validation\SubscriptionDuplicateValidator;
use WMDE\Fundraising\Frontend\SubscriptionContext\Validation\SubscriptionValidator;
use WMDE\Fundraising\Frontend\SubscriptionContext\Tests\Fixtures\InMemorySubscriptionRepository;
use WMDE\FunValidators\ConstraintViolation;
use WMDE\FunValidators\ValidationResult;
use WMDE\FunValidators\Validators\AllowedValuesValidator;
use WMDE\FunValidators\Validators\EmailValidator;
use WMDE\FunValidators\Validators\TextPolicyValidator;

/**
 * @covers \WMDE\Fundraising\Frontend\SubscriptionContext\Validation\SubscriptionValidator
 *
 * @license GNU GPL v2+
 * @author Gabriel Birke < gabriel.birke@wikimedia.de >
 */
class SubscriptionValidatorTest extends \PHPUnit\Framework\TestCase {

	private function getMockTextPolicyValidator(): TextPolicyValidator {
		$mock = $this->createMock( TextPolicyValidator::class );
		$mock->method( 'hasHarmlessContent' )
			->willReturn( true );
		return $mock;
	}

	private function getMockDuplicateValidator(): SubscriptionDuplicateValidator {
		$mock = $this->getMockBuilder( SubscriptionDuplicateValidator::class )
			->disableOriginalConstructor()->getMock();

		$mock->method( 'validate' )->willReturn( new ValidationResult() );
		return $mock;
	}

	private function createAddress( string $saluation, string $firstName, string $lastName ): Address {
		$address = new Address();
		$address->setSalutation( $saluation );
		$address->setFirstName( $firstName );
		$address->setLastName( $lastName );
		$address->setTitle( '' );
		$address->setCompany( '' );
		$address->setAddress( '' );
		$address->setCity( '' );
		$address->setPostcode( '' );
		return $address;
	}

	public function testEmailIsValidated(): void {
		$mailValidator = new EmailValidator( new NullDomainNameValidator() );
		$subscriptionValidator = new SubscriptionValidator(
			$mailValidator,
			$this->getMockTextPolicyValidator(),
			$this->getMockDuplicateValidator(),
			new AllowedValuesValidator( [''] )
		);
		$subscription = new Subscription();
		$subscription->setAddress( $this->createAddress( 'Herr', 'Nyan', 'Cat' ) );
		$subscription->setEmail( 'this is not a mail addess' );
		$this->assertConstraintWasViolated( $subscriptionValidator->validate( $subscription ), 'email' );
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

	public function testGivenBadWords_subscriptionIsStillValid(): void {
		$mailValidator = new EmailValidator( new NullDomainNameValidator() );
		$policyValidator = $this->createMock( TextPolicyValidator::class );
		$policyValidator->method( 'hasHarmlessContent' )
			->willReturn( false );
		$subscriptionValidator = new SubscriptionValidator(
			$mailValidator,
			$policyValidator,
			$this->getMockDuplicateValidator(),
			new AllowedValuesValidator( [''] )
		);
		$subscription = new Subscription();
		$subscription->setAddress( $this->createAddress( 'Herr', 'Nyan', 'Cat' ) );
		$subscription->setEmail( 'nyan@meow.com' );
		$this->assertTrue( $subscriptionValidator->validate( $subscription )->isSuccessful() );
	}

	public function testGivenBadWords_needsModerationIsTrue(): void {
		$mailValidator = new EmailValidator( new NullDomainNameValidator() );
		$policyValidator = $this->createMock( TextPolicyValidator::class );
		$policyValidator->method( 'hasHarmlessContent' )
			->willReturn( false );
		$subscriptionValidator = new SubscriptionValidator(
			$mailValidator,
			$policyValidator,
			$this->getMockDuplicateValidator(),
			new AllowedValuesValidator( [''] )
		);
		$subscription = new Subscription();
		$subscription->setAddress( $this->createAddress( 'Herr', 'Nyan', 'Cat' ) );
		$subscription->setEmail( 'nyan@meow.com' );
		$this->assertTrue( $subscriptionValidator->needsModeration( $subscription ) );
	}

	public function testDuplicateSubscriptionIsValidated(): void {
		$subscription = new Subscription();
		$subscription->setAddress( $this->createAddress( 'Herr', 'Nyan', 'Cat' ) );
		$subscription->setEmail( 'nyan@meow.com' );
		$subscription->setCreatedAt( new \DateTime( '5 minutes ago' ) );

		$mailValidator = new EmailValidator( new NullDomainNameValidator() );
		$repository = new InMemorySubscriptionRepository();
		$repository->storeSubscription( $subscription );
		$duplicateValidator = new SubscriptionDuplicateValidator( $repository, new \DateTime( '30 minutes ago' ) );
		$subscriptionValidator = new SubscriptionValidator(
			$mailValidator,
			$this->getMockTextPolicyValidator(),
			$duplicateValidator,
			new AllowedValuesValidator( [''] )
		);
		$this->assertConstraintWasViolated(
			$subscriptionValidator->validate( $subscription ),
			SubscriptionDuplicateValidator::SOURCE_NAME
		);
	}

	public function testHonorificIsValidated(): void {
		$subscription = new Subscription();
		$address = $this->createAddress( 'Herr', 'Nyan', 'Cat' );
		$address->setTitle( 'Overlord' );
		$subscription->setAddress( $address );
		$subscription->setEmail( 'nyan@meow.com' );

		$mailValidator = new EmailValidator( new NullDomainNameValidator() );
		$subscriptionValidator = new SubscriptionValidator(
			$mailValidator,
			$this->getMockTextPolicyValidator(),
			$this->getMockDuplicateValidator(),
			new AllowedValuesValidator( ['', 'Dr.', 'Prof.'] )
		);
		$this->assertConstraintWasViolated( $subscriptionValidator->validate( $subscription ), 'title' );
	}
}
