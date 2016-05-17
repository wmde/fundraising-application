<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\Unit\UseCases\ApplyForMembership;

use WMDE\Fundraising\Frontend\Tests\Data\ValidMembershipApplicationRequest;
use WMDE\Fundraising\Frontend\UseCases\ApplyForMembership\ApplicationValidationResult as Result;
use WMDE\Fundraising\Frontend\UseCases\ApplyForMembership\ApplyForMembershipRequest;
use WMDE\Fundraising\Frontend\UseCases\ApplyForMembership\MembershipApplicationValidator;
use WMDE\Fundraising\Frontend\Validation\MembershipFeeValidator;

/**
 * @covers WMDE\Fundraising\Frontend\UseCases\ApplyForMembership\MembershipApplicationValidator
 *
 * @license GNU GPL v2+
 * @author Kai Nissen < kai.nissen@wikimedia.de >
 */
class MembershipApplicationValidatorTest extends \PHPUnit_Framework_TestCase {

	public function testGivenValidRequest_validationSucceeds() {
		$validRequest = $this->newValidRequest();
		$response = $this->newValidator()->validate( $validRequest );

		$this->assertEquals( new Result( [] ), $response );
		$this->assertEmpty( $response->getViolationSources() );
		$this->assertTrue( $response->isSuccessful() );
	}

	public function testWhenFeeValidationFails_overallValidationAlsoFails() {
		$validRequest = $this->newValidRequest();
		$response = $this->newValidatorWithFailingFeeValidator()->validate( $validRequest );

		$this->assertEquals( $this->newFeeViolationResult(), $response );
		$this->assertNotEmpty( $response->getViolationSources() );
		$this->assertFalse( $response->isSuccessful() );
	}

	private function newValidator() {
		return new MembershipApplicationValidator( $this->newSucceedingFeeValidator() );
	}

	private function newValidatorWithFailingFeeValidator() {
		return new MembershipApplicationValidator( $this->newFailingFeeValidator() );
	}

	private function newFailingFeeValidator() {
		$feeValidator = $this->getMockBuilder( MembershipFeeValidator::class )->disableOriginalConstructor()->getMock();
		$feeValidator->method( 'validate' )
			->willReturn( $this->newFeeViolationResult() );
		return $feeValidator;
	}

	private function newSucceedingFeeValidator() {
		$feeValidator = $this->getMockBuilder( MembershipFeeValidator::class )->disableOriginalConstructor()->getMock();
		$feeValidator->method( 'validate' )
			->willReturn( new Result( [] ) );
		return $feeValidator;
	}

	private function newValidRequest(): ApplyForMembershipRequest {
		return ValidMembershipApplicationRequest::newValidRequest();
	}

	private function newFeeViolationResult() {
		return new Result( [
			Result::SOURCE_PAYMENT_AMOUNT => Result::VIOLATION_NOT_MONEY
		] );
	}

}
