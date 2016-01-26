<?php


namespace WMDE\Fundraising\Frontend\ResponseModel;

use WMDE\Fundraising\Entities\Request;

/**
 * @license GNU GPL v2+
 * @author Gabriel Birke < gabriel.birke@wikimedia.de >
 */
class AddSubscriptionResponse {

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

	public function isSuccessful() {
		return count( $this->validationErrors ) == 0;
	}
}