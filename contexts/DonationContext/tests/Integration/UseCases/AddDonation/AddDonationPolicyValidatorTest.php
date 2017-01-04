<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\DonationContext\Tests\Integration\UseCases\AddDonation;

use WMDE\Fundraising\Frontend\DonationContext\UseCases\AddDonation\AddDonationPolicyValidator;
use WMDE\Fundraising\Frontend\DonationContext\Tests\Data\ValidAddDonationRequest;
use WMDE\Fundraising\Frontend\Tests\Unit\Validation\ValidatorTestCase;
use WMDE\Fundraising\Frontend\Validation\AmountPolicyValidator;
use WMDE\Fundraising\Frontend\Validation\ConstraintViolation;
use WMDE\Fundraising\Frontend\Validation\TextPolicyValidator;
use WMDE\Fundraising\Frontend\Validation\ValidationResult;

/**
 * @covers WMDE\Fundraising\Frontend\DonationContext\UseCases\AddDonation\AddDonationPolicyValidator
 *
 * @licence GNU GPL v2+
 * @author Gabriel Birke < gabriel.birke@wikimedia.de >
 */
class AddDonationPolicyValidatorTest extends ValidatorTestCase {

	public function testTooHighAmountGiven_needsModerationReturnsTrue() {
		$policyValidator = new AddDonationPolicyValidator(
			$this->newFailingAmountValidator(),
			$this->newSucceedingTextPolicyValidator()
		);
		$this->assertTrue( $policyValidator->needsModeration( ValidAddDonationRequest::getRequest() ) );
	}

	public function testGivenBadWords_needsModerationReturnsTrue() {
		$policyValidator = new AddDonationPolicyValidator(
			$this->newSucceedingAmountValidator(),
			$this->newFailingTextPolicyValidator()
		);
		$this->assertTrue( $policyValidator->needsModeration( ValidAddDonationRequest::getRequest() ) );
	}

	private function newFailingAmountValidator(): AmountPolicyValidator {
		$amountPolicyValidator = $this->getMockBuilder( AmountPolicyValidator::class )
			->disableOriginalConstructor()
			->getMock();

		$amountPolicyValidator->method( 'validate' )->willReturn(
			new ValidationResult( new ConstraintViolation( 1000, 'too-high', 'amount' ) )
		);
		return $amountPolicyValidator;
	}

	private function newSucceedingAmountValidator(): AmountPolicyValidator {
		$amountPolicyValidator = $this->getMockBuilder( AmountPolicyValidator::class )
			->disableOriginalConstructor()
			->getMock();

		$amountPolicyValidator->method( 'validate' )->willReturn( new ValidationResult() );
		return $amountPolicyValidator;
	}

	private function newSucceedingTextPolicyValidator() {
		$succeedingTextPolicyValidator = $this->createMock( TextPolicyValidator::class );
		$succeedingTextPolicyValidator->method( 'textIsHarmless' )->willReturn( true );
		return $succeedingTextPolicyValidator;
	}

	private function newFailingTextPolicyValidator() {
		$failingTextPolicyValidator = $this->createMock( TextPolicyValidator::class );
		$failingTextPolicyValidator->method( 'hasHarmlessContent' )
			->willReturn( false );
		return $failingTextPolicyValidator;
	}

	public function testWhenEmailAddressIsNotBlacklisted_isAutoDeletedReturnsFalse() {
		$policyValidator = new AddDonationPolicyValidator(
			$this->newSucceedingAmountValidator(),
			$this->newSucceedingTextPolicyValidator(),
			[]
		);

		$this->assertFalse( $policyValidator->isAutoDeleted( ValidAddDonationRequest::getRequest() ) );
	}

	public function testWhenEmailAddressIsBlacklisted_isAutoDeletedReturnsTrue() {
		$policyValidator = new AddDonationPolicyValidator(
			$this->newSucceedingAmountValidator(),
			$this->newSucceedingTextPolicyValidator(),
			[ 'foo@bar.baz' ]
		);

		$this->assertTrue( $policyValidator->isAutoDeleted( ValidAddDonationRequest::getRequest() ) );
	}

}
