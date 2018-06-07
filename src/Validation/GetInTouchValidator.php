<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Validation;

use WMDE\Fundraising\Frontend\UseCases\GetInTouch\GetInTouchRequest;
use WMDE\FunValidators\CanValidateField;
use WMDE\FunValidators\ValidationResult;
use WMDE\FunValidators\Validators\EmailValidator;
use WMDE\FunValidators\Validators\RequiredFieldValidator;

/**
 * @license GNU GPL v2+
 * @author Kai Nissen < kai.nissen@wikimedia.de >
 */
class GetInTouchValidator {
	use CanValidateField;

	private $mailValidator;

	public function __construct( EmailValidator $mailValidator ) {
		$this->mailValidator = $mailValidator;
	}

	public function validate( GetInTouchRequest $instance ): ValidationResult {
		$requiredFieldValidator = new RequiredFieldValidator();

		return new ValidationResult( ...array_filter( [
			$this->getFieldViolation( $requiredFieldValidator->validate( $instance->getSubject() ), 'subject' ),
			$this->getFieldViolation( $requiredFieldValidator->validate( $instance->getCategory() ), 'category' ),
			$this->getFieldViolation( $requiredFieldValidator->validate( $instance->getMessageBody() ), 'messageBody' ),
			$this->getFieldViolation( $requiredFieldValidator->validate( $instance->getEmailAddress() ), 'email' ),
			$this->getFieldViolation( $this->mailValidator->validate( $instance->getEmailAddress() ), 'email' )
		] ) );
	}

}