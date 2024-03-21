<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Validation;

use WMDE\FunValidators\ConstraintViolation;
use WMDE\FunValidators\ValidationResult;
use WMDE\FunValidators\Validators\TextPolicyValidator;

class FieldTextPolicyValidator {

	private const VIOLATION_MESSAGE = 'This field has unacceptable language or URLs in it';

	public function __construct( private readonly TextPolicyValidator $textPolicyValidator ) {
	}

	public function validate( $value ): ValidationResult {	// @codingStandardsIgnoreLine
		if ( $value === '' ) {
			return new ValidationResult();
		}

		if ( $this->textPolicyValidator->textIsHarmless( (string)$value ) ) {
			return new ValidationResult();
		}

		return new ValidationResult( new ConstraintViolation( $value, self::VIOLATION_MESSAGE ) );
	}

}
