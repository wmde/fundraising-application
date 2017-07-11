<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\DonationContext\UseCases\AddDonation;

use WMDE\Fundraising\Frontend\DonationContext\UseCases\AddDonation\AddDonationValidationResult as Result;
use WMDE\Fundraising\Frontend\PaymentContext\Domain\Model\PaymentType;
use WMDE\Fundraising\Frontend\Validation\BankDataValidator;
use WMDE\Fundraising\Frontend\Validation\ConstraintViolation;
use WMDE\Fundraising\Frontend\Validation\EmailValidator;
use WMDE\Fundraising\Frontend\Validation\PaymentDataValidator;

/**
 * @license GNU GPL v2+
 * @author Gabriel Birke < gabriel.birke@wikimedia.de >
 */
class AddDonationValidator {
	private $paymentDataValidator;
	private $bankDataValidator;
	private $emailValidator;

	/**
	 * @var AddDonationRequest
	 */
	private $request;

	/**
	 * @var ConstraintViolation[]
	 */
	private $violations;

	private $maximumFieldLengths = [
		Result::SOURCE_DONOR_EMAIL => 250,
		Result::SOURCE_DONOR_COMPANY => 100,
		Result::SOURCE_DONOR_FIRST_NAME => 50,
		Result::SOURCE_DONOR_LAST_NAME => 50,
		Result::SOURCE_DONOR_SALUTATION => 16,
		Result::SOURCE_DONOR_TITLE => 16,
		Result::SOURCE_DONOR_STREET_ADDRESS => 100,
		Result::SOURCE_DONOR_POSTAL_CODE => 8,
		Result::SOURCE_DONOR_CITY => 100,
		Result::SOURCE_DONOR_COUNTRY => 8,
		Result::SOURCE_BANK_NAME => 100,
		Result::SOURCE_BIC => 32,
		Result::SOURCE_TRACKING_SOURCE => 250
	];

	public function __construct( PaymentDataValidator $paymentDataValidator, BankDataValidator $bankDataValidator,
								 EmailValidator $emailValidator ) {

		$this->paymentDataValidator = $paymentDataValidator;
		$this->bankDataValidator = $bankDataValidator;
		$this->emailValidator = $emailValidator;
	}

	public function validate( AddDonationRequest $addDonationRequest ): Result {
		$this->request = $addDonationRequest;
		$this->violations = [];

		$this->validateAmount();
		$this->validatePayment();
		$this->validateBankData();
		$this->validateDonorName();
		$this->validateDonorEmail();
		$this->validateDonorAddress();
		$this->validateTrackingData();

		return new Result( ...$this->violations );
	}

	private function validateAmount(): void {
		// TODO validate without euro class, put conversion in PaymentDataValidator
		$result = $this->paymentDataValidator->validate(
			$this->request->getAmount()->getEuroFloat(),
			$this->request->getPaymentType()
		);

		$violations = array_map( function( ConstraintViolation $violation ) {
			$violation->setSource( Result::SOURCE_PAYMENT_AMOUNT );
			return $violation;
		}, $result->getViolations() );
		$this->addViolations( $violations );
	}

	private function addViolations( array $violations ): void {
		$this->violations = array_merge( $this->violations, $violations );
	}

	private function validateBankData(): void {
		if ( $this->request->getPaymentType() !== PaymentType::DIRECT_DEBIT ) {
			return;
		}

		$bankData = $this->request->getBankData();
		$validationResult = $this->bankDataValidator->validate( $bankData );

		$this->addViolations( $validationResult->getViolations() );
		$this->validateFieldLength( $bankData->getBankName(), Result::SOURCE_BANK_NAME );
		$this->validateFieldLength( $bankData->getBic(), Result::SOURCE_BIC );
	}

	private function validateDonorEmail(): void {
		if ( $this->request->donorIsAnonymous() ) {
			return;
		}
		if ( $this->emailValidator->validate( $this->request->getDonorEmailAddress() )->hasViolations() ) {
			$this->addViolations( [ new ConstraintViolation(
				$this->request->getDonorEmailAddress(),
				Result::VIOLATION_MISSING,
				Result::SOURCE_DONOR_EMAIL
			) ] );
		} else {
			$this->validateFieldLength( $this->request->getDonorEmailAddress(), Result::SOURCE_DONOR_EMAIL );
		}
	}

	private function validateDonorName(): void {
		if ( $this->request->donorIsAnonymous() ) {
			return;
		}
		if ( $this->request->donorIsCompany() ) {
			$this->validateCompanyName();
		}
		else {
			$this->validatePersonName();
		}
	}

	private function validateCompanyName(): void {
		if ( $this->request->getDonorCompany() === '' ) {
			$this->violations[] = new ConstraintViolation(
				$this->request->getDonorCompany(),
				Result::VIOLATION_MISSING,
				Result::SOURCE_DONOR_COMPANY
			);
		} else {
			$this->validateFieldLength( $this->request->getDonorCompany(), Result::SOURCE_DONOR_COMPANY );
		}
	}

	private function validatePersonName(): void {
		$violations = [];

		if ( $this->request->getDonorFirstName() === '' ) {
			$violations[] = new ConstraintViolation(
				$this->request->getDonorFirstName(),
				Result::VIOLATION_MISSING,
				Result::SOURCE_DONOR_FIRST_NAME
			);
		} else {
			$this->validateFieldLength( $this->request->getDonorFirstName(), Result::SOURCE_DONOR_FIRST_NAME );
		}

		if ( $this->request->getDonorLastName() === '' ) {
			$violations[] = new ConstraintViolation(
				$this->request->getDonorLastName(),
				Result::VIOLATION_MISSING,
				Result::SOURCE_DONOR_LAST_NAME
			);
		} else {
			$this->validateFieldLength( $this->request->getDonorLastName(), Result::SOURCE_DONOR_LAST_NAME );
		}

		if ( $this->request->getDonorSalutation() === '' ) {
			$violations[] = new ConstraintViolation(
				$this->request->getDonorSalutation(),
				Result::VIOLATION_MISSING,
				Result::SOURCE_DONOR_SALUTATION
			);
		} else {
			$this->validateFieldLength( $this->request->getDonorSalutation(), Result::SOURCE_DONOR_SALUTATION );
		}

		$this->validateFieldLength( $this->request->getDonorTitle(), Result::SOURCE_DONOR_TITLE );
		// TODO: check if donor title is in the list of allowed titles?

		$this->addViolations( $violations );
	}

	private function validateDonorAddress(): void {
		if ( $this->request->donorIsAnonymous() ) {
			return;
		}

		$violations = [];

		if ( $this->request->getDonorStreetAddress() === '' ) {
			$violations[] = new ConstraintViolation(
				$this->request->getDonorStreetAddress(),
				Result::VIOLATION_MISSING,
				Result::SOURCE_DONOR_STREET_ADDRESS
			);
		} else {
			$this->validateFieldLength( $this->request->getDonorStreetAddress(), Result::SOURCE_DONOR_STREET_ADDRESS );
		}

		if ( $this->request->getDonorPostalCode() === '' ) {
			$violations[] = new ConstraintViolation(
				$this->request->getDonorPostalCode(),
				Result::VIOLATION_MISSING,
				Result::SOURCE_DONOR_POSTAL_CODE
			);
		} else {
			$this->validateFieldLength( $this->request->getDonorPostalCode(), Result::SOURCE_DONOR_POSTAL_CODE );
		}

		if ( $this->request->getDonorCity() === '' ) {
			$violations[] = new ConstraintViolation(
				$this->request->getDonorCity(),
				Result::VIOLATION_MISSING,
				Result::SOURCE_DONOR_CITY
			);
		} else {
			$this->validateFieldLength( $this->request->getDonorCity(), Result::SOURCE_DONOR_CITY );
		}

		if ( $this->request->getDonorCountryCode() === '' ) {
			$violations[] = new ConstraintViolation(
				$this->request->getDonorCountryCode(),
				Result::VIOLATION_MISSING,
				Result::SOURCE_DONOR_COUNTRY
			);
		} else {
			$this->validateFieldLength( $this->request->getDonorCountryCode(), Result::SOURCE_DONOR_COUNTRY );
		}

		if ( !preg_match( '/^\\d{4,5}$/', $this->request->getDonorPostalCode() ) ) {
			$violations[] = new ConstraintViolation(
				$this->request->getDonorPostalCode(),
				Result::VIOLATION_NOT_POSTCODE,
				Result::SOURCE_DONOR_POSTAL_CODE
			);
		}

		$this->addViolations( $violations );
	}

	private function validatePayment(): void {
		if ( ! in_array( $this->request->getPaymentType(), PaymentType::getPaymentTypes() ) ) {
			$this->violations[] = new ConstraintViolation(
				$this->request->getPaymentType(),
				Result::VIOLATION_WRONG_PAYMENT_TYPE,
				Result::SOURCE_PAYMENT_TYPE
			);
		}
	}

	private function validateFieldLength( string $value, string $fieldName ): void {
		if ( strlen( $value ) > $this->maximumFieldLengths[$fieldName] )  {
			$this->violations[] = new ConstraintViolation( $value, Result::VIOLATION_WRONG_LENGTH, $fieldName );
		}
	}

	private function validateTrackingData(): void {
		$this->validateFieldLength( $this->request->getSource(), Result::SOURCE_TRACKING_SOURCE );
		// validation of impression counts is not needed because input is converted to int
		// validation of skin, color and layout is not needed because they are static legacy values and empty.
	}
}