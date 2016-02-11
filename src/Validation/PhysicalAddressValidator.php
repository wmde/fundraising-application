<?php

namespace WMDE\Fundraising\Frontend\Validation;

use WMDE\Fundraising\Frontend\Domain\PhysicalAddress;

/**
 * @license GNU GPL v2+
 * @author Kai Nissen < kai.nissen@wikimedia.de >
 */
class PhysicalAddressValidator implements InstanceValidator {
	use CanValidateField;

	private $constraintViolations = [];

	/**
	 * @param PhysicalAddress $address
	 *
	 * @return bool
	 */
	public function validate( $address ): bool {
		$violations = [];
		$requiredFieldValidator = new RequiredFieldValidator();

		$violations[] = $this->validateField( $requiredFieldValidator, $address->getStreetAddress(), 'strasse' );
		$violations[] = $this->validateField( $requiredFieldValidator, $address->getPostalCode(), 'plz' );
		$violations[] = $this->validateField( $requiredFieldValidator, $address->getCity(), 'ort' );
		$violations[] = $this->validateField( $requiredFieldValidator, $address->getCountryCode(), 'country' );

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
