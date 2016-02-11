<?php

namespace WMDE\Fundraising\Frontend\Validation;

use WMDE\Fundraising\Frontend\Domain\Donation;

/**
 * @license GNU GPL v2+
 * @author Kai Nissen < kai.nissen@wikimedia.de >
 */
class DonationValidator implements InstanceValidator {
	use CanValidateField;

	private $nameValidator;
	private $addressValidator;
	private $mailValidator;
	private $amountValidator;
	private $amountPolicyValidator;

	protected $constraintViolations;

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

		$this->constraintViolations = [];
	}

	/**
	 * @param Donation $donation
	 *
	 * @return bool
	 */
	public function validate( $donation ): bool {
		$violations = [];
		$violations[] = $this->validateField( $this->amountValidator, $donation->getAmount(), 'betrag' );

		if ( $donation->getPersonalInfo() !== null ) {
			$violations = array_merge(
				$violations,
				$this->validateValueObject( $this->nameValidator, $donation->getPersonalInfo()->getPersonName() )
			);
			$violations = array_merge(
				$violations,
				$this->validateValueObject( $this->addressValidator, $donation->getPersonalInfo()->getPhysicalAddress() )
			);
			$violations[] = $this->validateField(
				$this->mailValidator,
				$donation->getPersonalInfo()->getEmailAddress(),
				'email'
			);
		}

		$this->constraintViolations = array_filter( $violations );
		return empty( $this->constraintViolations );
	}

	public function needsModeration( Donation $donation ): bool {
		// TODO: add TextPolicyValidator
		$this->policyViolations[] = $this->amountPolicyValidator->validate( $donation->getAmount() );
		return empty( $this->policyViolations );
	}

	/**
	 * @return ConstraintViolation[]
	 */
	public function getConstraintViolations(): array {
		return $this->constraintViolations;
	}

	/**
	 * @param InstanceValidator $validator
	 * @param $valueObject
	 * @return ConstraintViolation[]
	 */
	private function validateValueObject( InstanceValidator $validator, $valueObject ) {
		$validator->validate( $valueObject );
		return $validator->getConstraintViolations();
	}

}
