<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\Unit\UseCases\ApplyForMembership;

use WMDE\Fundraising\Frontend\Tests\Data\ValidMembershipApplicationRequest;
use WMDE\Fundraising\Frontend\UseCases\ApplyForMembership\ApplicationValidationResult as Result;
use WMDE\Fundraising\Frontend\UseCases\ApplyForMembership\ApplyForMembershipRequest;
use WMDE\Fundraising\Frontend\UseCases\ApplyForMembership\MembershipApplicationValidator;
use WMDE\Fundraising\Frontend\Validation\BankDataValidator;
use WMDE\Fundraising\Frontend\Validation\ConstraintViolation;
use WMDE\Fundraising\Frontend\Validation\MembershipFeeValidator;
use WMDE\Fundraising\Frontend\Validation\ValidationResult;

/**
 * @covers WMDE\Fundraising\Frontend\UseCases\ApplyForMembership\MembershipApplicationValidator
 *
 * @license GNU GPL v2+
 * @author Kai Nissen < kai.nissen@wikimedia.de >
 */
class MembershipApplicationValidatorTest extends \PHPUnit_Framework_TestCase {

	/*
	 * @var MembershipFeeValidator
	 */
	private $feeValidator;

	/**
	 * @var BankDataValidator
	 */
	private $bankDataValidator;

	public function setUp() {
		$this->feeValidator = $this->newSucceedingFeeValidator();
		$this->bankDataValidator = $this->newSucceedingBankDataValidator();
	}

	public function testGivenValidRequest_validationSucceeds() {
		$validRequest = $this->newValidRequest();
		$response = $this->newValidator()->validate( $validRequest );

		$this->assertEquals( new Result(), $response );
		$this->assertEmpty( $response->getViolationSources() );
		$this->assertTrue( $response->isSuccessful() );
	}

	private function newValidator() {
		return new MembershipApplicationValidator( $this->feeValidator, $this->bankDataValidator );
	}

	public function testWhenFeeValidationFails_overallValidationAlsoFails() {
		$this->feeValidator = $this->newFailingFeeValidator();

		$response = $this->newValidator()->validate( $this->newValidRequest() );

		$this->assertEquals( $this->newFeeViolationResult(), $response );
	}

	private function newFailingFeeValidator(): MembershipFeeValidator {
		$feeValidator = $this->getMockBuilder( MembershipFeeValidator::class )
			->disableOriginalConstructor()->getMock();

		$feeValidator->method( 'validate' )
			->willReturn( $this->newFeeViolationResult() );

		return $feeValidator;
	}

	private function newSucceedingFeeValidator(): MembershipFeeValidator {
		$feeValidator = $this->getMockBuilder( MembershipFeeValidator::class )
			->disableOriginalConstructor()->getMock();

		$feeValidator->method( 'validate' )
			->willReturn( new Result() );

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

	private function newSucceedingBankDataValidator(): BankDataValidator {
		$feeValidator = $this->getMockBuilder( BankDataValidator::class )
			->disableOriginalConstructor()->getMock();

		$feeValidator->method( 'validate' )
			->willReturn( new ValidationResult() );

		return $feeValidator;
	}

	/**
	 * @dataProvider bankDataViolationResultProvider
	 */
	public function testWhenBankDataValidationFails_overallValidationAlsoFails(
		ValidationResult $result, Result $expectedResult ) {

		$this->bankDataValidator = $this->newFailingBankDataValidator( $result );

		$this->assertEquals(
			$expectedResult,
			$this->newValidator()->validate( $this->newValidRequest() )
		);
	}

	private function newFailingBankDataValidator( ValidationResult $result ): BankDataValidator {
		$feeValidator = $this->getMockBuilder( BankDataValidator::class )
			->disableOriginalConstructor()->getMock();

		$feeValidator->method( 'validate' )
			->willReturn( $result );

		return $feeValidator;
	}

	public function bankDataViolationResultProvider() {
		return [
			[
				new ValidationResult( new ConstraintViolation( '', 'field_required', 'iban' ) ),
				new Result( [ Result::SOURCE_IBAN => Result::VIOLATION_MISSING ] )
			],
			[
				new ValidationResult( new ConstraintViolation( '', 'field_required', 'bic' ) ),
				new Result( [ Result::SOURCE_BIC => Result::VIOLATION_MISSING ] )
			],
			[
				new ValidationResult( new ConstraintViolation( '', 'field_required', 'bankname' ) ),
				new Result( [ Result::SOURCE_BANK_NAME => Result::VIOLATION_MISSING ] )
			]
		];
	}

}
