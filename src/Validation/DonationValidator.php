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
	private $amountPolicyValidator;

	private $policyViolations;

	public function __construct( AmountValidator $amountValidator,
								 AmountPolicyValidator $amountPolicyValidator,
								 PersonNameValidator $nameValidator,
								 PhysicalAddressValidator $addressValidator,
								 MailValidator $mailValidator ) {
		$this->nameValidator = $nameValidator;
		$this->addressValidator = $addressValidator;
		$this->mailValidator = $mailValidator;
		$this->amountValidator = $amountValidator;
		$this->amountPolicyValidator = $amountPolicyValidator;
	}

	public function validate( Donation $donation ): ValidationResult {
		$violations = [];
		$violations[] = $this->validateField( $this->amountValidator, $donation->getAmount(), 'betrag' );

		if ( $donation->getPersonalInfo() !== null ) {
			$violations = array_merge(
				$violations,
				$this->nameValidator->validate( $donation->getPersonalInfo()->getPersonName() )->getViolations()
			);
			$violations = array_merge(
				$violations,
				$this->addressValidator->validate( $donation->getPersonalInfo()->getPhysicalAddress() )->getViolations()
			);
			$violations[] = $this->validateField(
				$this->mailValidator,
				$donation->getPersonalInfo()->getEmailAddress(),
				'email'
			);
		}

		return new ValidationResult( ...array_filter( $violations ) );
	}

	public function needsModeration( Donation $donation ): bool {
		// TODO: add TextPolicyValidator
		$violations = [];

		$violations[] = $this->amountPolicyValidator->validate( $donation->getAmount(), $donation->getInterval() );

		$this->policyViolations = array_filter( $violations );
		return !empty( $this->policyViolations );
	}

}
