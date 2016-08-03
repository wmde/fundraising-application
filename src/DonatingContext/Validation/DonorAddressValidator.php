<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\DonatingContext\Validation;

use WMDE\Fundraising\Frontend\DonatingContext\Domain\Model\DonorAddress;
use WMDE\Fundraising\Frontend\Validation\CanValidateField;
use WMDE\Fundraising\Frontend\Validation\RequiredFieldValidator;
use WMDE\Fundraising\Frontend\Validation\ValidationResult;

/**
 * @license GNU GPL v2+
 * @author Kai Nissen < kai.nissen@wikimedia.de >
 */
class DonorAddressValidator {
	use CanValidateField;

	public function validate( DonorAddress $address ): ValidationResult {
		$validator = new RequiredFieldValidator();

		return new ValidationResult( ...array_filter( [
			$this->getFieldViolation( $validator->validate( $address->getStreetAddress() ), 'street' ),
			$this->getFieldViolation( $validator->validate( $address->getPostalCode() ), 'postcode' ),
			$this->getFieldViolation( $validator->validate( $address->getCity() ), 'city' ),
			$this->getFieldViolation( $validator->validate( $address->getCountryCode() ), 'country' )
		] ) );
	}

}
