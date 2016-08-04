<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Validation;

/**
 * @license GNU GPL v2+
 * @author Gabriel Birke < gabriel.birke@wikimedia.de >
 */
class ValidationResponse {

	private $validationErrors;
	private $needsModerationValue;

	/**
	 * @param ConstraintViolation[] $requestValidationErrors
	 * @param bool $needsModeration
	 */
	public function __construct( array $requestValidationErrors = [], $needsModeration = false ) {
		$this->validationErrors = $requestValidationErrors;
		$this->needsModerationValue = $needsModeration;
	}

	public static function newSuccessResponse(): self {
		return new self();
	}

	public static function newFailureResponse( array $errors ): self {
		return new self( $errors );
	}

	public static function newModerationNeededResponse(): self {
		return new self( [], true );
	}

	/**
	 * @return ConstraintViolation[]
	 */
	public function getValidationErrors(): array {
		return $this->validationErrors;
	}

	public function isSuccessful(): bool {
		return count( $this->validationErrors ) == 0;
	}

	public function needsModeration(): bool {
		return $this->needsModerationValue;
	}

}