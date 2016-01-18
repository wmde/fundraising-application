<?php


namespace WMDE\Fundraising\Frontend\Validation;

use WMDE\Fundraising\Entities\Request;

/**
 * @license GNU GPL v2+
 * @author Gabriel Birke < gabriel.birke@wikimedia.de >
 */
class RequestValidator implements InstanceValidator {

	use CanValidateField;

	private $mailValidator;
	private $constraintViolations;

	public function __construct( MailValidator $mailValidator ) {
		$this->mailValidator = $mailValidator;
		$this->constraintViolations = [];
	}

	/**
	 * @param Request $instance
	 * @return bool
	 */
	public function validate( $instance ): bool {
		$violations = [];
		$requiredFieldValidator = new RequiredFieldValidator();
		$violations[] = $this->validateField( $requiredFieldValidator, $instance->getAnrede(), 'anrede');
		$violations[] = $this->validateField( $requiredFieldValidator, $instance->getVorname(), 'vorname');
		$violations[] = $this->validateField( $requiredFieldValidator, $instance->getNachname(), 'nachname');
		$violations[] = $this->validateField( $requiredFieldValidator, $instance->getEmail(), 'email');
		$violations[] = $this->validateField( $this->mailValidator, $instance->getEmail(), 'email');
		$this->constraintViolations = array_filter( $violations );
		return count( $this->constraintViolations ) == 0;
	}

	public function getConstraintViolations(): array {
		return $this->constraintViolations;
	}

}