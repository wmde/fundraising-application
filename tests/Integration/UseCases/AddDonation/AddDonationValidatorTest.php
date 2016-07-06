<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\Integration\UseCases\AddDonation;

use WMDE\Fundraising\Frontend\Domain\Model\BankData;
use WMDE\Fundraising\Frontend\Domain\Model\Euro;
use WMDE\Fundraising\Frontend\Domain\Model\Iban;
use WMDE\Fundraising\Frontend\Domain\Model\PersonName;
use WMDE\Fundraising\Frontend\Tests\Data\ValidAddDonationRequest;
use WMDE\Fundraising\Frontend\Tests\Unit\Validation\ValidatorTestCase;
use WMDE\Fundraising\Frontend\UseCases\AddDonation\AddDonationValidationResult;
use WMDE\Fundraising\Frontend\UseCases\AddDonation\AddDonationValidator;
use WMDE\Fundraising\Frontend\Validation\AmountValidator;
use WMDE\Fundraising\Frontend\Validation\BankDataValidator;
use WMDE\Fundraising\Frontend\Validation\EmailValidator;
use WMDE\Fundraising\Frontend\Validation\IbanValidator;
use WMDE\Fundraising\Frontend\Validation\ValidationResult;

/**
 * @covers WMDE\Fundraising\Frontend\UseCases\AddDonation\AddDonationValidator
 *
 * @licence GNU GPL v2+
 * @author Kai Nissen < kai.nissen@wikimedia.de >
 * @author Gabriel Birke < gabriel.birke@wikimedia.de >
 */
class AddDonationValidatorTest extends ValidatorTestCase {

	/** @var AddDonationValidator */
	private $donationValidator;

	public function setUp() {
		$this->donationValidator = $this->newDonationValidator();
	}

	public function testGivenValidDonation_validationIsSuccessful() {
		$request = ValidAddDonationRequest::getRequest();
		$this->assertEmpty( $this->donationValidator->validate( $request )->getViolations() );
	}

	public function testGivenAnonymousDonorAndEmptyAddressFields_validatorReturnsNoViolations() {
		$request = ValidAddDonationRequest::getRequest();

		$request->setDonorType( PersonName::PERSON_ANONYMOUS );
		$request->setDonorSalutation( '' );
		$request->setDonorTitle( '' );
		$request->setDonorCompany( '' );
		$request->setDonorFirstName( '' );
		$request->setDonorLastName( '' );
		$request->setDonorStreetAddress( '' );
		$request->setDonorPostalCode( '' );
		$request->setDonorCity( '' );
		$request->setDonorCountryCode( '' );
		$request->setDonorEmailAddress( '' );

		$this->assertEmpty( $this->donationValidator->validate( $request )->getViolations() );
	}

	public function testGivenNoPaymentType_validatorReturnsFalse() {
		$request = ValidAddDonationRequest::getRequest();
		$request->setPaymentType( '' );

		$this->assertFalse( $this->donationValidator->validate( $request )->isSuccessful() );

		$this->assertConstraintWasViolated(
			$this->donationValidator->validate( $request ),
			AddDonationValidationResult::SOURCE_PAYMENT_TYPE
		);
	}

	public function testGivenUnsupportedPaymentType_validatorReturnsFalse() {
		$request = ValidAddDonationRequest::getRequest();
		$request->setPaymentType( 'KaiCoin' );

		$this->assertFalse( $this->donationValidator->validate( $request )->isSuccessful() );

		$this->assertConstraintWasViolated(
			$this->donationValidator->validate( $request ),
			AddDonationValidationResult::SOURCE_PAYMENT_TYPE
		);
	}

	public function testPersonalInfoValidationFails_validatorReturnsFalse() {
		$request = ValidAddDonationRequest::getRequest();
		$request->setDonorType( PersonName::PERSON_COMPANY );
		$request->setDonorCompany( '' );

		$this->assertFalse( $this->donationValidator->validate( $request )->isSuccessful() );

		$this->assertConstraintWasViolated(
			$this->donationValidator->validate( $request ),
			AddDonationValidationResult::SOURCE_DONOR_COMPANY
		);
	}

	public function testDirectDebitMissingBankData_validatorReturnsFalse() {
		$bankData = new BankData();
		$bankData->setIban( new Iban( '' ) );
		$bankData->setBic( '' );
		$bankData->setBankName( '' );
		$request = ValidAddDonationRequest::getRequest();
		$request->setBankData( $bankData );

		$result = $this->donationValidator->validate( $request );
		$this->assertFalse( $result->isSuccessful() );

		$this->assertConstraintWasViolated( $result, AddDonationValidationResult::SOURCE_IBAN );
		$this->assertConstraintWasViolated( $result, AddDonationValidationResult::SOURCE_BIC );
		$this->assertConstraintWasViolated( $result, AddDonationValidationResult::SOURCE_BANK_NAME );
	}

	public function testAmountTooLow_validatorReturnsFalse() {
		$request = ValidAddDonationRequest::getRequest();
		$request->setAmount( Euro::newFromCents( 50 ) );

		$result = $this->donationValidator->validate( $request );
		$this->assertFalse( $result->isSuccessful() );

		$this->assertConstraintWasViolated( $result, AddDonationValidationResult::SOURCE_PAYMENT_AMOUNT );
	}

	private function newDonationValidator(): AddDonationValidator {
		return new AddDonationValidator(
			new AmountValidator( 1.0 ),
			$this->newBankDataValidator(),
			$this->newMockEmailValidator()
		);
	}

	private function newBankDataValidator(): BankDataValidator {
		$ibanValidatorMock = $this->getMockBuilder( IbanValidator::class )->disableOriginalConstructor()->getMock();
		$ibanValidatorMock->method( 'validate' )
			->willReturn( new ValidationResult() );

		return new BankDataValidator( $ibanValidatorMock );
	}

	private function newMockEmailValidator() {
		$validator = $this->getMockBuilder( EmailValidator::class )->disableOriginalConstructor()->getMock();
		$validator->method( 'validate' )->willReturn( new ValidationResult() );
		return $validator;
	}
}
