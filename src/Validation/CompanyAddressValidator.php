<?php

namespace WMDE\Fundraising\Frontend\Validation;

use WMDE\Fundraising\Frontend\Domain\Address;
use WMDE\Fundraising\Frontend\Domain\Address\CompanyAddress;

/**
 * @license GNU GPL v2+
 * @author Kai Nissen < kai.nissen@wikimedia.de >
 */
class CompanyAddressValidator extends AddressValidator {

	use CanValidateField;

	/**
	 * @param Address $instance
	 * @return bool
	 */
	public function validate( $instance ): bool {
		/** @var CompanyAddress $instance */
		$violations = [];
		$requiredFieldValidator = new RequiredFieldValidator();
		$violations[] = $this->validateField( $requiredFieldValidator, $instance->getCompanyName(), 'firma');
		$this->constraintViolations = array_filter( $violations );

		parent::validate( $instance );

		return count( $this->constraintViolations ) == 0;
	}

}