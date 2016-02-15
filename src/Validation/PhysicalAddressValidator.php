<?php

declare(strict_types = 1);

namespace WMDE\Fundraising\Frontend\Validation;

use WMDE\Fundraising\Frontend\Domain\PhysicalAddress;

/**
 * @license GNU GPL v2+
 * @author Kai Nissen < kai.nissen@wikimedia.de >
 */
class PhysicalAddressValidator {
	use CanValidateField;

	public function validate( PhysicalAddress $address ): ValidationResult {
		$requiredFieldValidator = new RequiredFieldValidator();

		return new ValidationResult( ...array_filter( [
			$this->validateField( $requiredFieldValidator, $address->getStreetAddress(), 'strasse' ),
			$this->validateField( $requiredFieldValidator, $address->getPostalCode(), 'plz' ),
			$this->validateField( $requiredFieldValidator, $address->getCity(), 'ort' ),
			$this->validateField( $requiredFieldValidator, $address->getCountryCode(), 'country' )
		] ) );
	}

}
