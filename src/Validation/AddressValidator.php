<?php

namespace WMDE\Fundraising\Frontend\Validation;

use WMDE\Fundraising\Frontend\Domain\Address;

/**
 * @license GNU GPL v2+
 * @author Kai Nissen < kai.nissen@wikimedia.de >
 */
abstract class AddressValidator implements InstanceValidator {

	use CanValidateField;

	private $mailValidator;
	protected $constraintViolations;

	public function __construct( MailValidator $mailValidator ) {
		$this->mailValidator = $mailValidator;
		$this->constraintViolations = [];
	}

	/**
	 * @param Address $instance
	 * @return bool
	 */
	public function validate( $instance ): bool {
		$violations = [];
		$requiredFieldValidator = new RequiredFieldValidator();
		$violations[] = $this->validateField( $requiredFieldValidator, $instance->getAddress(), 'anrede' );
		$violations[] = $this->validateField( $requiredFieldValidator, $instance->getPostcode(), 'plz' );
		$violations[] = $this->validateField( $requiredFieldValidator, $instance->getCity(), 'ort' );
		$violations[] = $this->validateField( $requiredFieldValidator, $instance->getCountryCode(), 'country' );
		$violations[] = $this->validateField( $this->mailValidator, $instance->getEmail(), 'email' );

		$this->constraintViolations = array_merge( $this->constraintViolations, array_filter( $violations ) );

		return count( $this->constraintViolations ) == 0;
	}

	public function getConstraintViolations(): array {
		return $this->constraintViolations;
	}

}