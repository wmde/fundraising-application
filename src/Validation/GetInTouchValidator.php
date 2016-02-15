<?php

declare(strict_types = 1);

namespace WMDE\Fundraising\Frontend\Validation;

use WMDE\Fundraising\Frontend\UseCases\GetInTouch\GetInTouchRequest;

/**
 * @license GNU GPL v2+
 * @author Kai Nissen < kai.nissen@wikimedia.de >
 */
class GetInTouchValidator {
	use CanValidateField;

	private $mailValidator;

	public function __construct( MailValidator $mailValidator ) {
		$this->mailValidator = $mailValidator;
	}

	public function validate( GetInTouchRequest $instance ): ValidationResult {
		$requiredFieldValidator = new RequiredFieldValidator();

		return new ValidationResult( ...array_filter( [
			$this->validateField( $requiredFieldValidator, $instance->getSubject(), 'subject' ),
			$this->validateField( $requiredFieldValidator, $instance->getMessageBody(), 'messageBody' ),
			$this->validateField( $requiredFieldValidator, $instance->getEmailAddress(), 'email' ),
			$this->validateField( $this->mailValidator, $instance->getEmailAddress(), 'email' )
		] ) );
	}

}