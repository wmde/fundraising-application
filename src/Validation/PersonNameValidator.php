<?php

declare(strict_types = 1);

namespace WMDE\Fundraising\Frontend\Validation;

use WMDE\Fundraising\Frontend\Domain\PersonName;
use WMDE\Fundraising\Frontend\ResponseModel\ValidationResponse;

/**
 * @license GNU GPL v2+
 * @author Kai Nissen < kai.nissen@wikimedia.de >
 */
class PersonNameValidator {
	use CanValidateField;

	public function validate( PersonName $name ): ValidationResult {
		if ( $name->getPersonType() === PersonName::PERSON_PRIVATE ) {
			return $this->validatePrivatePerson( $name );
		}

		return $this->validateCompanyPerson( $name );
	}

	private function validatePrivatePerson( PersonName $instance ): ValidationResult {
		$requiredFieldValidator = new RequiredFieldValidator();

		return new ValidationResult( ...array_filter( [
			$this->validateField( $requiredFieldValidator, $instance->getSalutation(), 'anrede' ),
			$this->validateField( $requiredFieldValidator, $instance->getFirstName(), 'vorname' ),
			$this->validateField( $requiredFieldValidator, $instance->getLastName(), 'nachname' )
		] ) );
	}

	private function validateCompanyPerson( PersonName $instance ): ValidationResult {
		return new ValidationResult( ...array_filter( [
			$this->validateField( new RequiredFieldValidator(), $instance->getCompanyName(), 'firma' )
		] ) );
	}

}
