<?php

namespace WMDE\Fundraising\Frontend\Validation;

use WMDE\Fundraising\Frontend\Domain\Address;

/**
 * @license GNU GPL v2+
 * @author Kai Nissen < kai.nissen@wikimedia.de >
 */
class PhysicalAddressValidator implements InstanceValidator {
	use CanValidateField;

	private $constraintViolations = [];

	public function validate( $instance ): bool {
		$violations = [];
		$requiredFieldValidator = new RequiredFieldValidator();
		$violations[] = $this->validateField( $requiredFieldValidator, $instance->getStreetAddress(), 'strasse' );
		$violations[] = $this->validateField( $requiredFieldValidator, $instance->getPostalCode(), 'plz' );
		$violations[] = $this->validateField( $requiredFieldValidator, $instance->getCity(), 'ort' );
		$violations[] = $this->validateField( $requiredFieldValidator, $instance->getCountryCode(), 'country' );

		$this->constraintViolations = array_merge( $this->constraintViolations, array_filter( $violations ) );

		return empty( $this->constraintViolations );
	}

	/**
	 * @return ConstraintViolation[]
	 */
	public function getConstraintViolations(): array {
		return $this->constraintViolations;
	}

}
