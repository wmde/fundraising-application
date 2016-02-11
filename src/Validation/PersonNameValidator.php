<?php

namespace WMDE\Fundraising\Frontend\Validation;

use WMDE\Fundraising\Frontend\Domain\PersonName;

/**
 * @license GNU GPL v2+
 * @author Kai Nissen < kai.nissen@wikimedia.de >
 */
class PersonNameValidator implements InstanceValidator {
	use CanValidateField;

	private $constraintViolations = [];

	/**
	 * @param PersonName $name
	 *
	 * @return bool
	 */
	public function validate( $name ): bool {
		if ( $name->getPersonType() === PersonName::PERSON_PRIVATE ) {
			return $this->validatePrivatePerson( $name );
		}

		return $this->validateCompanyPerson( $name );
	}

	/**
	 * @return ConstraintViolation[]
	 */
	public function getConstraintViolations(): array {
		return $this->constraintViolations;
	}

	private function validatePrivatePerson( PersonName $instance ): bool {
		$violations = [];

		$requiredFieldValidator = new RequiredFieldValidator();
		$violations[] = $this->validateField( $requiredFieldValidator, $instance->getSalutation(), 'anrede' );
		$violations[] = $this->validateField( $requiredFieldValidator, $instance->getFirstName(), 'vorname' );
		$violations[] = $this->validateField( $requiredFieldValidator, $instance->getLastName(), 'nachname' );

		$this->constraintViolations = array_merge( $this->constraintViolations, array_filter( $violations ) );
		return empty( $this->constraintViolations );
	}

	private function validateCompanyPerson( PersonName $instance ): bool {
		$violations = [];

		$requiredFieldValidator = new RequiredFieldValidator();
		$violations[] = $this->validateField( $requiredFieldValidator, $instance->getCompanyName(), 'firma' );

		$this->constraintViolations = array_merge( $this->constraintViolations, array_filter( $violations ) );
		return empty( $this->constraintViolations );
	}

}
