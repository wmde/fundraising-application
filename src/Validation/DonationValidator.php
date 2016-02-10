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

	protected $constraintViolations;

	public function __construct( PersonNameValidator $nameValidator,
								 PhysicalAddressValidator $addressValidator,
								 MailValidator $mailValidator ) {
		$this->nameValidator = $nameValidator;
		$this->addressValidator = $addressValidator;
		$this->mailValidator = $mailValidator;

		$this->constraintViolations = [];
	}

	/**
	 * @param Donation $donation
	 *
	 * @return bool
	 */
	public function validate( $donation ): bool {
		$violations = [];

		if ( $donation->getPersonalInfo() ) {
			$violations += $this->validateValueObject( $this->nameValidator, $donation->getPersonalInfo()->getPersonName() );
			$violations += $this->validateValueObject( $this->addressValidator, $donation->getPersonalInfo()->getPhysicalAddress() );
			$violations[] = $this->validateField(
				$this->mailValidator,
				$donation->getPersonalInfo()->getEmailAddress()->getFullAddress(),
				'email'
			);
		}

		$this->constraintViolations = array_filter( $violations );
		return empty( $this->constraintViolations );
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