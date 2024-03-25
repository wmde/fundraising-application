<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Presentation\Presenters;

use WMDE\FunValidators\ConstraintViolation;
use WMDE\FunValidators\ValidationResponse;

class AddSubscriptionJsonPresenter {

	/**
	 * @return array<string, string|array<string, string>>
	 */
	public function present( ValidationResponse $subscriptionResponse ): array {
		if ( $subscriptionResponse->isSuccessful() ) {
			return $this->newSuccessResponse();
		}

		return $this->newErrorResponse( $subscriptionResponse );
	}

	/**
	 * @return array<string, string>
	 */
	private function newSuccessResponse(): array {
		return [ 'status' => 'OK' ];
	}

	/**
	 * @return array<string, string|array<string, string>>
	 */
	private function newErrorResponse( ValidationResponse $response ): array {
		$errors = [];
		/** @var ConstraintViolation $constraintViolation */
		foreach ( $response->getValidationErrors() as $constraintViolation ) {
			$errors[$constraintViolation->getSource()] = $constraintViolation->getMessageIdentifier();
		}
		return [ 'status' => 'ERR', 'errors' => $errors ];
	}
}
