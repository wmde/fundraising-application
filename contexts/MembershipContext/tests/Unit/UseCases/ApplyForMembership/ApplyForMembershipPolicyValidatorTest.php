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

	/** @dataProvider blacklistedEmailAddressProvider */
	public function testWhenEmailAddressIsBlacklisted_isAutoDeletedReturnsTrue( $emailAddress ) {
		$policyValidator = $this->newPolicyValidatorWithEmailBlacklist();
		$this->assertTrue(
			$policyValidator->isAutoDeleted(
				ValidMembershipApplication::newDomainEntityWithEmailAddress( $emailAddress )
			)
		);
	}

	public function blacklistedEmailAddressProvider() {
		return [
			[ 'foo@bar.baz' ],
			[ 'test@example.com' ],
			[ 'Test@EXAMPLE.com' ]
		];
	}

	/** @dataProvider allowedEmailAddressProvider */
	public function testWhenEmailAddressIsNotBlacklisted_isAutoDeletedReturnsFalse( $emailAddress ) {
		$policyValidator = $this->newPolicyValidatorWithEmailBlacklist();
		$this->assertFalse(
			$policyValidator->isAutoDeleted(
				ValidMembershipApplication::newDomainEntityWithEmailAddress( $emailAddress )
			)
		);
	}

	public function allowedEmailAddressProvider() {
		return [
			[ 'other.person@bar.baz' ],
			[ 'test@example.computer.says.no' ],
			[ 'some.person@gmail.com' ]
		];
	}

	/**
	 * @return ApplyForMembershipPolicyValidator
	 */
	private function newPolicyValidatorWithEmailBlacklist(): ApplyForMembershipPolicyValidator {
		$textPolicyValidator = $this->newSucceedingTextPolicyValidator();
		$policyValidator = new ApplyForMembershipPolicyValidator(
			$textPolicyValidator,
			[ '/^foo@bar\.baz$/', '/@example.com$/i' ]
		);

		return $policyValidator;
	}

}
