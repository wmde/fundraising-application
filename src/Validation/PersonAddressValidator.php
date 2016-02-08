<?php

namespace WMDE\Fundraising\Frontend\Validation;

use WMDE\Fundraising\Frontend\Domain\Address;
use WMDE\Fundraising\Frontend\Domain\Address\PersonAddress;

/**
 * @license GNU GPL v2+
 * @author Kai Nissen < kai.nissen@wikimedia.de >
 */
class PersonAddressValidator extends AddressValidator {

	use CanValidateField;

	/**
	 * @param Address $instance
	 * @return bool
	 */
	public function validate( $instance ): bool {
		/** @var PersonAddress $instance */
		$violations = [];
		$requiredFieldValidator = new RequiredFieldValidator();
		$violations[] = $this->validateField( $requiredFieldValidator, $instance->getSalutation(), 'anrede');
		$violations[] = $this->validateField( $requiredFieldValidator, $instance->getFirstName(), 'vorname');
		$violations[] = $this->validateField( $requiredFieldValidator, $instance->getLastName(), 'nachname');
		$violations[] = $this->validateField( $requiredFieldValidator, $instance->getTitle(), 'email');
		$this->constraintViolations = array_filter( $violations );

		parent::validate( $instance );

		return count( $this->constraintViolations ) == 0;
	}

}
