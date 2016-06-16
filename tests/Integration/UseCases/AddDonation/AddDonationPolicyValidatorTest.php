<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\Integration\UseCases\AddDonation;

use WMDE\Fundraising\Frontend\Tests\Data\ValidAddDonationRequest;
use WMDE\Fundraising\Frontend\Tests\Unit\Validation\ValidatorTestCase;
use WMDE\Fundraising\Frontend\UseCases\AddDonation\AddDonationPolicyValidator;
use WMDE\Fundraising\Frontend\Validation\AmountPolicyValidator;
use WMDE\Fundraising\Frontend\Validation\ConstraintViolation;
use WMDE\Fundraising\Frontend\Validation\TextPolicyValidator;
use WMDE\Fundraising\Frontend\Validation\ValidationResult;

/**
 * @covers WMDE\Fundraising\Frontend\UseCases\AddDonation\AddDonationPolicyValidator
 *
 * @licence GNU GPL v2+
 * @author Gabriel Birke < gabriel.birke@wikimedia.de >
 */
class AddDonationPolicyValidatorTest extends ValidatorTestCase {

	public function testTooHighAmountGiven_needsModerationReturnsTrue() {
		$succeedingTextPolicy = $this->createMock( TextPolicyValidator::class );
		$succeedingTextPolicy->method( 'textIsHarmless' )->willReturn( true );
		$policyValidator = new AddDonationPolicyValidator(
			$this->newFailingAmountValidator(),
			$succeedingTextPolicy
		);
		$this->assertTrue( $policyValidator->needsModeration( ValidAddDonationRequest::getRequest() ) );
	}

	public function testGivenBadWords_needsModerationReturnsTrue() {
		$failingTextPolicyValidator = $this->createMock( TextPolicyValidator::class );
		$failingTextPolicyValidator->method( 'hasHarmlessContent' )
			->willReturn( false );

		$policyValidator = new AddDonationPolicyValidator(
			$this->newSucceedingAmountValidator(),
			$failingTextPolicyValidator
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
}
