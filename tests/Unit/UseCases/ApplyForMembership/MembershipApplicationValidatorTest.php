<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\Unit\UseCases\ApplyForMembership;

use WMDE\Fundraising\Frontend\Domain\Model\Iban;
use WMDE\Fundraising\Frontend\Tests\Data\ValidMembershipApplicationRequest;
use WMDE\Fundraising\Frontend\UseCases\ApplyForMembership\ApplicationValidationResult as Result;
use WMDE\Fundraising\Frontend\UseCases\ApplyForMembership\ApplyForMembershipRequest;
use WMDE\Fundraising\Frontend\UseCases\ApplyForMembership\MembershipApplicationValidator;
use WMDE\Fundraising\Frontend\Validation\BankDataValidator;
use WMDE\Fundraising\Frontend\Validation\IbanValidator;
use WMDE\Fundraising\Frontend\Validation\MembershipFeeValidator;
use WMDE\Fundraising\Frontend\Validation\ValidationResult;

/**
 * @covers WMDE\Fundraising\Frontend\UseCases\ApplyForMembership\MembershipApplicationValidator
 *
 * @license GNU GPL v2+
 * @author Kai Nissen < kai.nissen@wikimedia.de >
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
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

	public function testWhenIbanIsMissing_validationFails() {
		$this->bankDataValidator = $this->newRealBankDataValidator();

		$request = $this->newValidRequest();
		$request->getPaymentBankData()->setIban( new Iban( '' ) );

		$this->assertEquals(
			new Result( [ Result::SOURCE_IBAN => Result::VIOLATION_MISSING ] ),
			$this->newValidator()->validate( $request )
		);
	}

	private function newRealBankDataValidator(): BankDataValidator {
		return new BankDataValidator( $this->newSucceedingIbanValidator() );
	}

	private function newSucceedingIbanValidator(): IbanValidator {
		$ibanValidator = $this->getMockBuilder( IbanValidator::class )
			->disableOriginalConstructor()->getMock();

		$ibanValidator->method( 'validate' )
			->willReturn( new ValidationResult() );

		return $ibanValidator;
	}

	public function testWhenBicIsMissing_validationFails() {
		$this->bankDataValidator = $this->newRealBankDataValidator();

		$request = $this->newValidRequest();
		$request->getPaymentBankData()->setBic( '' );

		$this->assertEquals(
			new Result( [ Result::SOURCE_BIC => Result::VIOLATION_MISSING ] ),
			$this->newValidator()->validate( $request )
		);
	}

	public function testWhenBankNameIsMissing_validationFails() {
		$this->bankDataValidator = $this->newRealBankDataValidator();

		$request = $this->newValidRequest();
		$request->getPaymentBankData()->setBankName( '' );

		$this->assertEquals(
			new Result( [ Result::SOURCE_BANK_NAME => Result::VIOLATION_MISSING ] ),
			$this->newValidator()->validate( $request )
		);
	}

	public function testWhenBankCodeIsMissing_validationFails() {
		$this->bankDataValidator = $this->newRealBankDataValidator();

		$request = $this->newValidRequest();
		$request->getPaymentBankData()->setBankCode( '' );

		$this->assertEquals(
			new Result( [ Result::SOURCE_BANK_CODE => Result::VIOLATION_MISSING ] ),
			$this->newValidator()->validate( $request )
		);
	}

	public function testWhenBankAccountIsMissing_validationFails() {
		$this->bankDataValidator = $this->newRealBankDataValidator();

		$request = $this->newValidRequest();
		$request->getPaymentBankData()->setAccount( '' );

		$this->assertEquals(
			new Result( [ Result::SOURCE_BANK_ACCOUNT => Result::VIOLATION_MISSING ] ),
			$this->newValidator()->validate( $request )
		);
	}

	public function testWhenTooLongBankAccount_validationFails() {
		$this->bankDataValidator = $this->newRealBankDataValidator();

		$request = $this->newValidRequest();
		$request->getPaymentBankData()->setAccount( '01189998819991197253' );

		$this->assertEquals(
			new Result( [ Result::SOURCE_BANK_ACCOUNT => Result::VIOLATION_WRONG_LENGTH ] ),
			$this->newValidator()->validate( $request )
		);
	}

	public function testWhenTooLongBankCode_validationFails() {
		$this->bankDataValidator = $this->newRealBankDataValidator();

		$request = $this->newValidRequest();
		$request->getPaymentBankData()->setBankCode( '01189998819991197253' );

		$this->assertEquals(
			new Result( [ Result::SOURCE_BANK_CODE => Result::VIOLATION_WRONG_LENGTH ] ),
			$this->newValidator()->validate( $request )
		);
	}

}
