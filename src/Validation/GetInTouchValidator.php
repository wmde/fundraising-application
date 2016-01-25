<?php

declare(strict_types = 1);

namespace WMDE\Fundraising\Frontend\Validation;

use WMDE\Fundraising\Frontend\UseCases\GetInTouch\GetInTouchRequest;

/**
 * @license GNU GPL v2+
 * @author Kai Nissen < kai.nissen@wikimedia.de >
 */
class GetInTouchValidator implements InstanceValidator {
	use CanValidateField;

	private $mailValidator;
	private $constraintViolations;

	public function __construct( MailValidator $mailValidator ) {
		$this->mailValidator = $mailValidator;
		$this->constraintViolations = [];
	}

	/**
	 * @param GetInTouchRequest $instance
	 * @return bool
	 */
	public function validate( $instance ): bool {
		$violations = [];
		$requiredFieldValidator = new RequiredFieldValidator();
		$violations[] = $this->validateField( $requiredFieldValidator, $instance->getSubject(), 'Betreff');
		$violations[] = $this->validateField( $requiredFieldValidator, $instance->getMessageBody(), 'kommentar');
		$violations[] = $this->validateField( $requiredFieldValidator, $instance->getEmailAddress(), 'email');
		$violations[] = $this->validateField( $this->mailValidator, $instance->getEmailAddress(), 'email');
		$this->constraintViolations = array_filter( $violations );
		return count( $this->constraintViolations ) == 0;
	}

	public function getConstraintViolations(): array {
		return $this->constraintViolations;
	}

}