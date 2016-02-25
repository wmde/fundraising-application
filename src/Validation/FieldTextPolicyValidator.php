<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Validation;

/**
 * @license GNU GPL v2+
 * @author Gabriel Birke < gabriel.birke@wikimedia.de >
 */
class FieldTextPolicyValidator {

	const VIOLATION_MESSAGE = 'This field has unacceptable language or URLs in it';

	private $validationFlags;
	private $textPolicyValidator;

	public function __construct( TextPolicyValidator $textPolicyValidator, int $validationFlags = 0 ) {
		$this->textPolicyValidator = $textPolicyValidator;
		$this->validationFlags = $validationFlags;
	}

	public function validate( $value ): ValidationResult {
		if ( $value === '' ) {
			return new ValidationResult();
		}

		if ( $this->textPolicyValidator->hasHarmlessContent( (string) $value, $this->validationFlags ) ) {
			return new ValidationResult();
		}

		return new ValidationResult( new ConstraintViolation( $value, self::VIOLATION_MESSAGE ) );
	}

}