<?php

namespace WMDE\Fundraising\Frontend\Validation;

/**
 * @license GNU GPL v2+
 * @author Gabriel Birke < gabriel.birke@wikimedia.de >
 */
class FieldTextPolicyValidator implements ScalarValueValidator {

	const VIOLATION_MESSAGE = 'This field has unacceptable language or URLs in it';

	private $lastViolation = null;
	private $validationFlags;
	private $textPolicyValidator;

	public function __construct( TextPolicyValidator $textPolicyValidator, int $validationFlags = 0 ) {
		$this->textPolicyValidator = $textPolicyValidator;
		$this->validationFlags = $validationFlags;
	}

	public function validate( $value ): bool {
		if ( $value === '' ) {
			return true;
		}
		if ( $this->textPolicyValidator->hasHarmlessContent( (string) $value, $this->validationFlags ) ) {
			return true;
		}
		$this->lastViolation = new ConstraintViolation( $value, self::VIOLATION_MESSAGE );
		return false;
	}

	public function getLastViolation(): ConstraintViolation {
		return $this->lastViolation;
	}

}