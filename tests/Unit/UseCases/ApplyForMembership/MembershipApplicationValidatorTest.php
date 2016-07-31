<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\Unit\UseCases\ApplyForMembership;

use WMDE\Fundraising\Frontend\MembershipApplicationContext\UseCases\ApplyForMembership\ApplicationValidationResult as Result;
use WMDE\Fundraising\Frontend\MembershipApplicationContext\UseCases\ApplyForMembership\ApplyForMembershipRequest;
use WMDE\Fundraising\Frontend\MembershipApplicationContext\UseCases\ApplyForMembership\MembershipApplicationValidator;
use WMDE\Fundraising\Frontend\PaymentContext\Domain\Model\Iban;
use WMDE\Fundraising\Frontend\Tests\Data\ValidMembershipApplicationRequest;
use WMDE\Fundraising\Frontend\Tests\Fixtures\SucceedingEmailValidator;
use WMDE\Fundraising\Frontend\Validation\BankDataValidator;
use WMDE\Fundraising\Frontend\Validation\ConstraintViolation;
use WMDE\Fundraising\Frontend\Validation\EmailValidator;
use WMDE\Fundraising\Frontend\Validation\IbanValidator;
use WMDE\Fundraising\Frontend\Validation\MembershipFeeValidator;
use WMDE\Fundraising\Frontend\Validation\ValidationResult;

/**
 * @covers WMDE\Fundraising\Frontend\Validation\MembershipFeeValidator
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

	/**
	 * @var EmailValidator
	 */
	private $emailValidator;

	public function setUp() {
		$this->feeValidator = $this->newSucceedingFeeValidator();
		$this->bankDataValidator = $this->newSucceedingBankDataValidator();
		$this->emailValidator = new SucceedingEmailValidator();
	}

	public function testGivenValidRequest_validationSucceeds() {
		$validRequest = $this->newValidRequest();
		$response = $this->newValidator()->validate( $validRequest );

		$this->assertEquals( new Result(), $response );
		$this->assertEmpty( $response->getViolationSources() );
		$this->assertTrue( $response->isSuccessful() );
	}

	private function newValidator() {
		return new MembershipApplicationValidator(
			$this->feeValidator,
			$this->bankDataValidator,
			$this->emailValidator
		);
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

		$this->assertRequestValidationResultInErrors(
			$request,
			[ Result::SOURCE_IBAN => Result::VIOLATION_MISSING ]
		);
	}

	private function assertRequestValidationResultInErrors( ApplyForMembershipRequest $request, array $expectedErrors ) {
		$this->assertEquals(
			new Result( $expectedErrors ),
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

		$this->assertRequestValidationResultInErrors(
			$request,
			[ Result::SOURCE_BIC => Result::VIOLATION_MISSING ]
		);
	}

	public function testWhenBankNameIsMissing_validationFails() {
		$this->bankDataValidator = $this->newRealBankDataValidator();

		$request = $this->newValidRequest();
		$request->getPaymentBankData()->setBankName( '' );

		$this->assertRequestValidationResultInErrors(
			$request,
			[ Result::SOURCE_BANK_NAME => Result::VIOLATION_MISSING ]
		);
	}

	public function testWhenBankCodeIsMissing_validationFails() {
		$this->bankDataValidator = $this->newRealBankDataValidator();

		$request = $this->newValidRequest();
		$request->getPaymentBankData()->setBankCode( '' );

		$this->assertRequestValidationResultInErrors(
			$request,
			[ Result::SOURCE_BANK_CODE => Result::VIOLATION_MISSING ]
		);
	}

	public function testWhenBankAccountIsMissing_validationFails() {
		$this->bankDataValidator = $this->newRealBankDataValidator();

		$request = $this->newValidRequest();
		$request->getPaymentBankData()->setAccount( '' );

		$this->assertRequestValidationResultInErrors(
			$request,
			[ Result::SOURCE_BANK_ACCOUNT => Result::VIOLATION_MISSING ]
		);
	}

	public function testWhenTooLongBankAccount_validationFails() {
		$this->bankDataValidator = $this->newRealBankDataValidator();

		$request = $this->newValidRequest();
		$request->getPaymentBankData()->setAccount( '01189998819991197253' );

		$this->assertRequestValidationResultInErrors(
			$request,
			[ Result::SOURCE_BANK_ACCOUNT => Result::VIOLATION_WRONG_LENGTH ]
		);
	}

	public function testWhenTooLongBankCode_validationFails() {
		$this->bankDataValidator = $this->newRealBankDataValidator();

		$request = $this->newValidRequest();
		$request->getPaymentBankData()->setBankCode( '01189998819991197253' );

		$this->assertRequestValidationResultInErrors(
			$request,
			[ Result::SOURCE_BANK_CODE => Result::VIOLATION_WRONG_LENGTH ]
		);
	}

	public function testWhenDateOfBirthIsNotDate_validationFails() {
		$request = $this->newValidRequest();
		$request->setApplicantDateOfBirth( 'this is not a valid date' );

		$this->assertRequestValidationResultInErrors(
			$request,
			[ Result::SOURCE_APPLICANT_DATE_OF_BIRTH => Result::VIOLATION_NOT_DATE ]
		);
	}

	/**
	 * @dataProvider invalidPhoneNumberProvider
	 */
	public function testWhenApplicantPhoneNumberIsInvalid_validationFails( string $invalidPhoneNumber ) {
		$request = $this->newValidRequest();
		$request->setApplicantPhoneNumber( $invalidPhoneNumber );

		$this->assertRequestValidationResultInErrors(
			$request,
			[ Result::SOURCE_APPLICANT_PHONE_NUMBER => Result::VIOLATION_NOT_PHONE_NUMBER ]
		);
	}

	public function invalidPhoneNumberProvider() {
		return [
			'potato' => [ 'potato' ],

			// TODO: we use the regex from the old app, which allows for lots of bugus. Improve when time
//			'number plus stuff' => [ '01189998819991197253 (invalid edition)' ],
		];
	}

	/**
	 * @dataProvider emailViolationTypeProvider
	 */
	public function testWhenApplicantEmailIsInvalid_validationFails( string $emailViolationType ) {
		$this->emailValidator = $this->newFailingEmailValidator( $emailViolationType );

		$request = $this->newValidRequest();
		$request->setApplicantEmailAddress( 'this is not a valid email' );

		$this->assertRequestValidationResultInErrors(
			$request,
			[ Result::SOURCE_APPLICANT_EMAIL => Result::VIOLATION_NOT_EMAIL ]
		);
	}

	public function emailViolationTypeProvider() {
		return [
			[ 'email_address_wrong_format' ],
			[ 'email_address_invalid' ],
			[ 'email_address_domain_record_not_found' ],
		];
	}

	/**
	 * @param string $violationType
	 *
	 * @return EmailValidator
	 */
	private function newFailingEmailValidator( string $violationType ): EmailValidator {
		$feeValidator = $this->getMockBuilder( EmailValidator::class )
			->disableOriginalConstructor()->getMock();

		$feeValidator->method( 'validate' )
			->willReturn( new ValidationResult( new ConstraintViolation( 'this is not a valid email', $violationType ) ) );

		return $feeValidator;
	}

	public function testWhenCompanyIsMissingFromCompanyApplication_validationFails() {
		$request = $this->newValidRequest();
		$request->markApplicantAsCompany();
		$request->setApplicantCompanyName( '' );

		$this->assertRequestValidationResultInErrors(
			$request,
			[ Result::SOURCE_APPLICANT_COMPANY => Result::VIOLATION_MISSING ]
		);
	}

	public function testWhenFirstNameIsMissingFromPersonalApplication_validationFails() {
		$request = $this->newValidRequest();
		$request->setApplicantFirstName( '' );

		$this->assertRequestValidationResultInErrors(
			$request,
			[ Result::SOURCE_APPLICANT_FIRST_NAME => Result::VIOLATION_MISSING ]
		);
	}

	public function testWhenLastNameIsMissingFromPersonalApplication_validationFails() {
		$request = $this->newValidRequest();
		$request->setApplicantLastName( '' );

		$this->assertRequestValidationResultInErrors(
			$request,
			[ Result::SOURCE_APPLICANT_LAST_NAME => Result::VIOLATION_MISSING ]
		);
	}

	public function testWhenSalutationIsMissingFromPersonalApplication_validationFails() {
		$request = $this->newValidRequest();
		$request->setApplicantSalutation( '' );

		$this->assertRequestValidationResultInErrors(
			$request,
			[ Result::SOURCE_APPLICANT_SALUTATION => Result::VIOLATION_MISSING ]
		);
	}

	public function testWhenStreetAddressIsMissing_validationFails() {
		$request = $this->newValidRequest();
		$request->setApplicantStreetAddress( '' );

		$this->assertRequestValidationResultInErrors(
			$request,
			[ Result::SOURCE_APPLICANT_STREET_ADDRESS => Result::VIOLATION_MISSING ]
		);
	}

	public function testWhenPostalCodeIsMissing_validationFails() {
		$request = $this->newValidRequest();
		$request->setApplicantPostalCode( '' );

		$this->assertRequestValidationResultInErrors(
			$request,
			[ Result::SOURCE_APPLICANT_POSTAL_CODE => Result::VIOLATION_MISSING ]
		);
	}

	public function testWhenCityIsMissing_validationFails() {
		$request = $this->newValidRequest();
		$request->setApplicantCity( '' );

		$this->assertRequestValidationResultInErrors(
			$request,
			[ Result::SOURCE_APPLICANT_CITY => Result::VIOLATION_MISSING ]
		);
	}

	public function testWhenCountryCodeIsMissing_validationFails() {
		$request = $this->newValidRequest();
		$request->setApplicantCountryCode( '' );

		$this->assertRequestValidationResultInErrors(
			$request,
			[ Result::SOURCE_APPLICANT_COUNTRY => Result::VIOLATION_MISSING ]
		);
	}

	public function testPhoneNumberIsNotProvided_validationDoesNotFail() {
		$request = $this->newValidRequest();
		$request->setApplicantPhoneNumber( '' );

		$this->assertTrue( $this->newValidator()->validate( $request )->isSuccessful() );
	}

	public function testDateOfBirthIsNotProvided_validationDoesNotFail() {
		$request = $this->newValidRequest();
		$request->setApplicantDateOfBirth( '' );

		$this->assertTrue( $this->newValidator()->validate( $request )->isSuccessful() );
	}

	public function testPersonalInfoWithLongFields_validationFails() {
		$longText = str_repeat( 'Cats ', 500 );
		$request = $this->newValidRequest();
		$request->setApplicantFirstName( $longText );
		$request->setApplicantLastName( $longText );
		$request->setApplicantTitle( $longText );
		$request->setApplicantSalutation( $longText );
		$request->setApplicantStreetAddress( $longText );
		$request->setApplicantPostalCode( $longText );
		$request->setApplicantCity( $longText );
		$request->setApplicantCountryCode( $longText );
		$this->assertRequestValidationResultInErrors(
			$request,
			[
				Result::SOURCE_APPLICANT_FIRST_NAME => Result::VIOLATION_WRONG_LENGTH,
				Result::SOURCE_APPLICANT_LAST_NAME => Result::VIOLATION_WRONG_LENGTH,
				Result::SOURCE_APPLICANT_SALUTATION => Result::VIOLATION_WRONG_LENGTH,
				Result::SOURCE_APPLICANT_STREET_ADDRESS => Result::VIOLATION_WRONG_LENGTH,
				Result::SOURCE_APPLICANT_POSTAL_CODE => Result::VIOLATION_WRONG_LENGTH,
				Result::SOURCE_APPLICANT_CITY => Result::VIOLATION_WRONG_LENGTH,
				Result::SOURCE_APPLICANT_COUNTRY => Result::VIOLATION_WRONG_LENGTH
			]
		);
	}

	public function testContactInfoWithLongFields_validationFails() {
		$request = $this->newValidRequest();
		$request->setApplicantEmailAddress( str_repeat( 'Cats', 500 ) . '@example.com' );
		$request->setApplicantPhoneNumber( str_repeat( '1234', 500 ) );

		$this->assertRequestValidationResultInErrors(
			$request,
			[
				Result::SOURCE_APPLICANT_EMAIL => Result::VIOLATION_WRONG_LENGTH,
				Result::SOURCE_APPLICANT_PHONE_NUMBER => Result::VIOLATION_WRONG_LENGTH
			]
		);
	}

	public function testBankDataWithLongFields_validationFails() {
		$longText = str_repeat( 'Cats ', 500 );
		$request = $this->newValidRequest();
		$bankData = $request->getPaymentBankData();
		$bankData->setBic( $longText );
		$bankData->setBankName( $longText );
		// Other length violations will be caught by IBAN validation

		$this->assertRequestValidationResultInErrors(
			$request,
			[
				Result::SOURCE_BANK_NAME => Result::VIOLATION_WRONG_LENGTH,
				Result::SOURCE_BIC => Result::VIOLATION_WRONG_LENGTH
			]
		);
	}

}
