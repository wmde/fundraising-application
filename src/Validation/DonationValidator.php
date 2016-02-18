<?php

namespace WMDE\Fundraising\Frontend\Validation;

use WMDE\Fundraising\Frontend\Domain\Donation;

/**
 * @license GNU GPL v2+
 * @author Kai Nissen < kai.nissen@wikimedia.de >
 */
class DonationValidator {
	use CanValidateField;

	private $nameValidator;
	private $addressValidator;
	private $mailValidator;
	private $amountValidator;
	private $paymentTypeValidator;
	private $amountPolicyValidator;
	private $bankDataValidator;
	private $textPolicyValidator;

	private $policyViolations;

	public function __construct( AmountValidator $amountValidator,
								 AmountPolicyValidator $amountPolicyValidator,
								 TextPolicyValidator $textPolicyValidator,
								 PersonNameValidator $nameValidator,
								 PhysicalAddressValidator $addressValidator,
								 AllowedValuesValidator $paymentTypeValidator,
								 BankDataValidator $bankDataValidator,
								 MailValidator $mailValidator ) {
		// TODO: inject a ValidatorObject to reduce number of parameters
		$this->nameValidator = $nameValidator;
		$this->addressValidator = $addressValidator;
		$this->mailValidator = $mailValidator;
		$this->amountValidator = $amountValidator;
		$this->paymentTypeValidator = $paymentTypeValidator;
		$this->amountPolicyValidator = $amountPolicyValidator;
		$this->bankDataValidator = $bankDataValidator;
		$this->textPolicyValidator = $textPolicyValidator;
	}

	public function validate( Donation $donation ): ValidationResult {
		$violations = [
			$this->getAmountViolation( $donation ),
			$this->getFieldViolation(
				$this->paymentTypeValidator->validate( $donation->getPaymentType() ), 'zahlweise'
			)
		];

		if ( $donation->getPersonalInfo() !== null ) {
			$violations = array_merge(
				$violations,
				$this->nameValidator->validate( $donation->getPersonalInfo()->getPersonName() )->getViolations()
			);
			$violations = array_merge(
				$violations,
				$this->addressValidator->validate( $donation->getPersonalInfo()->getPhysicalAddress() )->getViolations()
			);
			$violations[] = $this->getFieldViolation(
				$this->mailValidator->validate( $donation->getPersonalInfo()->getEmailAddress() ),
				'email'
			);
		}

		if ( $donation->getPaymentType() === 'BEZ' ) {
			$violations = array_merge(
				$violations,
				$this->bankDataValidator->validate( $donation->getBankData() )->getViolations()
			);
		}

		return new ValidationResult( ...array_filter( $violations ) );
	}

	private function getAmountViolation( Donation $donation ) {
		return $this->getFieldViolation(
			$this->amountValidator->validate(
				$donation->getAmount(),
				$donation->getPaymentType()
			),
			'betrag'
		);
	}

	public function needsModeration( Donation $donation ): bool {
		$violations = [];

		$violations[] = $this->getFieldViolation(
			$this->amountPolicyValidator->validate(
				$donation->getAmount(),
				$donation->getInterval()
			),
			'betrag'
		);

		$violations = array_merge(
			$violations,
			$this->getBadWordViolations( $donation )
		);

		$this->policyViolations = array_filter( $violations );

		return !empty( $this->policyViolations );
	}

	public function getPolicyViolations() {
		return $this->policyViolations;
	}

	private function getBadWordViolations( Donation $donation ) {
		$violations = [];

		$flags = TextPolicyValidator::CHECK_BADWORDS |
			TextPolicyValidator::IGNORE_WHITEWORDS |
			TextPolicyValidator::CHECK_URLS;
		$fieldTextValidator = new FieldTextPolicyValidator( $this->textPolicyValidator, $flags );
		$personalInfo = $donation->getPersonalInfo();

		if ( $personalInfo ) {
			$violations[] = $this->getFieldViolation(
				$fieldTextValidator->validate( $personalInfo->getPersonName()->getFirstName() ),
				'vorname'
			);
			$violations[] = $this->getFieldViolation(
				$fieldTextValidator->validate( $personalInfo->getPersonName()->getLastName() ),
				'nachname'
			);
			$violations[] = $this->getFieldViolation(
				$fieldTextValidator->validate( $personalInfo->getPersonName()->getCompanyName() ),
				'firma'
			);
			$violations[] = $this->getFieldViolation(
				$fieldTextValidator->validate( $personalInfo->getPhysicalAddress()->getStreetAddress() ),
				'strasse'
			);
			$violations[] = $this->getFieldViolation(
				$fieldTextValidator->validate( $personalInfo->getPhysicalAddress()->getPostalCode() ),
				'plz'
			);
			$violations[] = $this->getFieldViolation(
				$fieldTextValidator->validate( $personalInfo->getPhysicalAddress()->getCity() ),
				'ort'
			);
		}

		return $violations;
	}

}
