<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Validation;

use WMDE\Fundraising\Frontend\Domain\Model\PhysicalAddress;

/**
 * @license GNU GPL v2+
 * @author Kai Nissen < kai.nissen@wikimedia.de >
 */
class PhysicalAddressValidator {
	use CanValidateField;

	public function validate( PhysicalAddress $address ): ValidationResult {
		$validator = new RequiredFieldValidator();

		return new ValidationResult( ...array_filter( [
			$this->getFieldViolation( $validator->validate( $address->getStreetAddress() ), 'street' ),
			$this->getFieldViolation( $validator->validate( $address->getPostalCode() ), 'postcode' ),
			$this->getFieldViolation( $validator->validate( $address->getCity() ), 'city' ),
			$this->getFieldViolation( $validator->validate( $address->getCountryCode() ), 'country' )
		] ) );
	}

}
