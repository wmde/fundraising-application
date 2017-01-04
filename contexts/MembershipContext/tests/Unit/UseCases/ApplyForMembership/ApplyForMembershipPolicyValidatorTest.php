<?php


namespace WMDE\Fundraising\Frontend\MembershipContext\Tests\Unit\UseCases\ApplyForMembership;

use WMDE\Fundraising\Frontend\MembershipContext\Tests\Data\ValidMembershipApplication;
use WMDE\Fundraising\Frontend\MembershipContext\UseCases\ApplyForMembership\ApplyForMembershipPolicyValidator;
use WMDE\Fundraising\Frontend\Validation\TextPolicyValidator;

class ApplyForMembershipPolicyValidatorTest extends \PHPUnit\Framework\TestCase {

	public function testGivenQuarterlyAmountTooHigh_MembershipApplicationNeedsModeration() {
		$textPolicyValidator = $this->newSucceedingTextPolicyValidator();
		$policyValidator = new ApplyForMembershipPolicyValidator( $textPolicyValidator );
		$this->assertTrue( $policyValidator->needsModeration(
			ValidMembershipApplication::newApplicationWithTooHighQuarterlyAmount()
		) );
	}

	private function newSucceedingTextPolicyValidator(): TextPolicyValidator {
		$textPolicyValidator = $this->createMock( TextPolicyValidator::class );
		$textPolicyValidator->method( 'textIsHarmless' )->willReturn( true );
		return $textPolicyValidator;
	}

	public function testGivenYearlyAmountTooHigh_MembershipApplicationNeedsModeration() {
		$textPolicyValidator = $this->newSucceedingTextPolicyValidator();
		$policyValidator = new ApplyForMembershipPolicyValidator( $textPolicyValidator );
		$this->assertTrue( $policyValidator->needsModeration(
			ValidMembershipApplication::newApplicationWithTooHighYearlyAmount()
		) );
	}

	public function testFailingTextPolicyValidation_MembershipApplicationNeedsModeration() {
		$textPolicyValidator = $this->createMock( TextPolicyValidator::class );
		$textPolicyValidator->method( 'textIsHarmless' )->willReturn( false );
		$policyValidator = new ApplyForMembershipPolicyValidator( $textPolicyValidator );
		$this->assertTrue( $policyValidator->needsModeration(
			ValidMembershipApplication::newDomainEntity()
		) );
	}

	public function testWhenEmailAddressIsBlacklisted_isAutoDeletedReturnsTrue() {
		$textPolicyValidator = $this->newSucceedingTextPolicyValidator();
		$policyValidator = new ApplyForMembershipPolicyValidator(
			$textPolicyValidator,
			[ ValidMembershipApplication::APPLICANT_EMAIL_ADDRESS ]
		);
		$this->assertTrue( $policyValidator->isAutoDeleted( ValidMembershipApplication::newDomainEntity() ) );
	}

}
