<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Validation;

use WMDE\Fundraising\Frontend\DonatingContext\Domain\Model\DonorName;

/**
 * @license GNU GPL v2+
 * @author Kai Nissen < kai.nissen@wikimedia.de >
 */
class PersonNameValidator {
	use CanValidateField;

	public function validate( DonorName $name ): ValidationResult {
		if ( $name->getPersonType() === DonorName::PERSON_PRIVATE ) {
			return $this->validatePrivatePerson( $name );
		}

		return $this->validateCompanyPerson( $name );
	}

	private function validatePrivatePerson( DonorName $instance ): ValidationResult {
		$validator = new RequiredFieldValidator();

		return new ValidationResult( ...array_filter( [
			$this->getFieldViolation( $validator->validate( $instance->getSalutation() ), 'salutation' ),
			$this->getFieldViolation( $validator->validate( $instance->getFirstName() ), 'firstName' ),
			$this->getFieldViolation( $validator->validate( $instance->getLastName() ), 'lastName' )
		] ) );
	}

	private function validateCompanyPerson( DonorName $instance ): ValidationResult {
		return new ValidationResult( ...array_filter( [
			$this->getFieldViolation( ( new RequiredFieldValidator() )->validate( $instance->getCompanyName() ), 'company' )
		] ) );
	}

}
