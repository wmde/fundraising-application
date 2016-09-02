<?php

namespace WMDE\Fundraising\Frontend\DonatingContext\Validation;

use WMDE\Fundraising\Frontend\DonatingContext\Domain\Model\Donor;
use WMDE\Fundraising\Frontend\Validation\CanValidateField;
use WMDE\Fundraising\Frontend\Validation\EmailValidator;
use WMDE\Fundraising\Frontend\Validation\ValidationResult;

/**
 * @license GNU GPL v2+
 * @author Gabriel Birke < gabriel.birke@wikimedia.de >
 */
class DonorValidator {
	use CanValidateField;

	private $nameValidator;
	private $addressValidator;
	private $mailValidator;

	public function __construct( DonorNameValidator $nameValidator,
								 DonorAddressValidator $addressValidator,
								 EmailValidator $mailValidator ) {
		$this->nameValidator = $nameValidator;
		$this->addressValidator = $addressValidator;
		$this->mailValidator = $mailValidator;
	}

	public function validate( Donor $personalInfo ): ValidationResult {
		$violations = $this->nameValidator->validate( $personalInfo->getName() )->getViolations();
		$violations = array_merge(
			$violations,
			$this->addressValidator->validate( $personalInfo->getPhysicalAddress() )->getViolations()
		);
		$violations[] = $this->getFieldViolation(
			$this->mailValidator->validate( $personalInfo->getEmailAddress() ),
			'email'
		);

		return new ValidationResult( ...array_filter( $violations ) );
	}

}
