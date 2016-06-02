<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\UseCases\ApplyForMembership;

use WMDE\Fundraising\Frontend\UseCases\ApplyForMembership\ApplicationValidationResult as Result;
use WMDE\Fundraising\Frontend\Validation\BankDataValidator;
use WMDE\Fundraising\Frontend\Validation\ConstraintViolation;
use WMDE\Fundraising\Frontend\Validation\EmailValidator;
use WMDE\Fundraising\Frontend\Validation\MembershipFeeValidator as FeeValidator;

/**
 * @license GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class MembershipApplicationValidator {

	private $feeValidator;
	private $bankDataValidator;
	private $emailValidator;

	/**
	 * @var ApplyForMembershipRequest
	 */
	private $request;

	/**
	 * @var string[] ApplicationValidationResult::SOURCE_ => ApplicationValidationResult::VIOLATION_
	 */
	private $violations;

	public function __construct( FeeValidator $feeValidator, BankDataValidator $bankDataValidator,
		EmailValidator $emailValidator ) {

		$this->feeValidator = $feeValidator;
		$this->bankDataValidator = $bankDataValidator;
		$this->emailValidator = $emailValidator;
	}

	public function validate( ApplyForMembershipRequest $applicationRequest ): Result {
		$this->request = $applicationRequest;
		$this->violations = [];

		$this->validateFee();
		$this->validateBankData();
		$this->validateApplicantName();
		$this->validateApplicantContactInfo();
		$this->validateApplicantDateOfBirth();

		return new Result( $this->violations );
	}

	private function validateFee() {
		$result = $this->feeValidator->validate(
			$this->request->getPaymentAmountInEuros(),
			$this->request->getPaymentIntervalInMonths(),
			$this->getApplicantType()
		);

		$this->addViolations( $result->getViolations() );
	}

	private function getApplicantType() {
		return $this->request->isCompanyApplication() ?
			FeeValidator::APPLICANT_TYPE_COMPANY : FeeValidator::APPLICANT_TYPE_PERSON;
	}

	private function addViolations( array $violations ) {
		$this->violations = array_merge( $this->violations, $violations );
	}

	private function validateBankData() {
		$validationResult = $this->bankDataValidator->validate( $this->request->getPaymentBankData() );
		$violations = [];

		foreach ( $validationResult->getViolations() as $violation ) {
			$violations[$this->getBankDataViolationSource( $violation )] = $this->getBankDataViolationType( $violation );
		}

		$this->addViolations( $violations );
	}

	private function getBankDataViolationSource( ConstraintViolation $violation ): string {
		switch ( $violation->getSource() ) {
			case 'iban':
				return Result::SOURCE_IBAN;
			case 'bic':
				return Result::SOURCE_BIC;
			case 'bankname':
				return Result::SOURCE_BANK_NAME;
			case 'blz':
				return Result::SOURCE_BANK_CODE;
			case 'konto':
				return Result::SOURCE_BANK_ACCOUNT;
			default:
				throw new \LogicException();
		}
	}

	private function getBankDataViolationType( ConstraintViolation $violation ): string {
		switch ( $violation->getMessageIdentifier() ) {
			case 'field_required':
				return Result::VIOLATION_MISSING;
			case 'incorrect_length':
				return Result::VIOLATION_WRONG_LENGTH;
			case 'iban_blocked':
				return Result::VIOLATION_IBAN_BLOCKED;
			case 'iban_invalid':
				return Result::VIOLATION_IBAN_INVALID;
			default:
				throw new \LogicException();
		}
	}

	private function validateApplicantDateOfBirth() {
		if ( !strtotime( $this->request->getApplicantDateOfBirth() ) ) {
			$this->violations[Result::SOURCE_APPLICANT_DATE_OF_BIRTH] = Result::VIOLATION_NOT_DATE;
		}
	}

	private function validateApplicantContactInfo() {
		if ( !preg_match( '/^[0-9\+\-\(\)]+/i', $this->request->getApplicantPhoneNumber() ) ) {
			$this->violations[Result::SOURCE_APPLICANT_PHONE_NUMBER] = Result::VIOLATION_NOT_PHONE_NUMBER;
		}

		if ( $this->emailValidator->validate( $this->request->getApplicantEmailAddress() )->hasViolations() ) {
			$this->violations[Result::SOURCE_APPLICANT_EMAIL] = Result::VIOLATION_NOT_EMAIL;
		}
	}

	private function validateApplicantName() {
		if ( $this->request->isCompanyApplication() ) {
			$this->validateCompanyName();
		}
		else {
			$this->validatePersonName();
		}
	}

	private function validateCompanyName() {
		if ( $this->request->getApplicantCompanyName() === '' ) {
			$this->violations[Result::SOURCE_APPLICANT_COMPANY] = Result::VIOLATION_MISSING;
		}
	}

	private function validatePersonName() {
		if ( $this->request->getApplicantFirstName() === '' ) {
			$this->violations[Result::SOURCE_APPLICANT_FIRST_NAME] = Result::VIOLATION_MISSING;
		}

		if ( $this->request->getApplicantLastName() === '' ) {
			$this->violations[Result::SOURCE_APPLICANT_LAST_NAME] = Result::VIOLATION_MISSING;
		}

		if ( $this->request->getApplicantSalutation() === '' ) {
			$this->violations[Result::SOURCE_APPLICANT_SALUTATION] = Result::VIOLATION_MISSING;
		}
	}

}