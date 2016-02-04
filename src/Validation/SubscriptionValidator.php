<?php


namespace WMDE\Fundraising\Frontend\Validation;

use WMDE\Fundraising\Entities\Subscription;

/**
 * @license GNU GPL v2+
 * @author Gabriel Birke < gabriel.birke@wikimedia.de >
 */
class SubscriptionValidator implements InstanceValidator {

	use CanValidateField;

	private $mailValidator;
	private $constraintViolations;

	public function __construct( MailValidator $mailValidator ) {
		$this->mailValidator = $mailValidator;
		$this->constraintViolations = [];
	}

	/**
	 * @param Subscription $instance
	 * @return bool
	 */
	public function validate( $instance ): bool {
		$violations = [];
		$requiredFieldValidator = new RequiredFieldValidator();
		$address = $instance->getAddress();
		$violations[] = $this->validateField( $requiredFieldValidator, $address->getSalutation(), 'salutation');
		$violations[] = $this->validateField( $requiredFieldValidator, $address->getFirstName(), 'firstName');
		$violations[] = $this->validateField( $requiredFieldValidator, $address->getLastName(), 'lastName');
		$violations[] = $this->validateField( $requiredFieldValidator, $instance->getEmail(), 'email');
		$violations[] = $this->validateField( $this->mailValidator, $instance->getEmail(), 'email');
		$this->constraintViolations = array_filter( $violations );
		return count( $this->constraintViolations ) == 0;
	}

	public function getConstraintViolations(): array {
		return $this->constraintViolations;
	}

}