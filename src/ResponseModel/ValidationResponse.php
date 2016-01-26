<?php

namespace WMDE\Fundraising\Frontend\ResponseModel;

/**
 * @license GNU GPL v2+
 * @author Gabriel Birke < gabriel.birke@wikimedia.de >
 */
class ValidationResponse {

	private $validationErrors;

	public function __construct( array $requestValidationErrors = [] ) {
		$this->validationErrors = $requestValidationErrors;
	}

	public static function newSuccessResponse(): self {
		return new self();
	}

	public static function newFailureResponse( array $errors ): self {
		return new self( $errors );
	}

	public function getValidationErrors(): array {
		return $this->validationErrors;
	}

	public function isSuccessful(): bool {
		return count( $this->validationErrors ) == 0;
	}

}