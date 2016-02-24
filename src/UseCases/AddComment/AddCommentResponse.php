<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\UseCases\AddComment;

/**
 * @license GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class AddCommentResponse {

	public static function newSuccessResponse(): self {
		return new self();
	}

	public static function newFailureResponse( string $errorMessage ): self {
		return new self( $errorMessage );
	}

	private $errorMessage;

	private function __construct( string $errorMessage = '' ) {
		$this->errorMessage = $errorMessage;
	}

	public function isSuccessful(): bool {
		return $this->errorMessage === '';
	}

	/**
	 * Returns the error message, or empty string in case the request was a success.
	 * @return string
	 */
	public function getErrorMessage(): string {
		return $this->errorMessage;
	}

}