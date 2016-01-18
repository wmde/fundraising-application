<?php


namespace WMDE\Fundraising\Frontend\ResponseModel;

use WMDE\Fundraising\Entities\Request;

/**
 * @license GNU GPL v2+
 * @author Gabriel Birke < gabriel.birke@wikimedia.de >
 */
class AddSubscriptionResponse {

	private $request;
	private $validationErrors;

	public function __construct( Request $request, array $requestValidationErrors = [] ) {
		$this->request = $request;
		$this->validationErrors = $requestValidationErrors;
	}

	public static function newSuccessResponse( Request $request ): self {
		return new self( $request );
	}

	public static function newFailureResponse( Request $request, array $errors ): self {
		return new self( $request, $errors );
	}

	public function getRequest(): Request {
		return $this->request;
	}

	public function getValidationErrors(): array {
		return $this->validationErrors;
	}

	public function isSuccessful() {
		return count( $this->validationErrors ) == 0;
	}
}