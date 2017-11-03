<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Validation;

use WMDE\FunValidators\ConstraintViolation;
use WMDE\FunValidators\ValidationResult;
use WMDE\FunValidators\Validators\TextPolicyValidator;

/**
 * @license GNU GPL v2+
 * @author Gabriel Birke < gabriel.birke@wikimedia.de >
 */
class FieldTextPolicyValidator {

	const VIOLATION_MESSAGE = 'This field has unacceptable language or URLs in it';

	private $textPolicyValidator;

	public function __construct( TextPolicyValidator $textPolicyValidator ) {
		$this->textPolicyValidator = $textPolicyValidator;
	}

	public function validate( $value ): ValidationResult {	// @codingStandardsIgnoreLine
		if ( $value === '' ) {
			return new ValidationResult();
		}

		if ( $this->textPolicyValidator->textIsHarmless( (string) $value ) ) {
			return new ValidationResult();
		}

		return new ValidationResult( new ConstraintViolation( $value, self::VIOLATION_MESSAGE ) );
	}

}