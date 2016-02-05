<?php

namespace WMDE\Fundraising\Frontend\Presentation\Presenters;

use WMDE\Fundraising\Frontend\ResponseModel\ValidationResponse;
use WMDE\Fundraising\Frontend\Validation\ConstraintViolation;

/**
 * @licence GNU GPL v2+
 * @author Gabriel Birke < gabriel.birke@wikimedia.de >
 */
class AddSubscriptionJSONPresenter {

	public function present( ValidationResponse $subscriptionResponse ): array {
		if ( $subscriptionResponse->isSuccessful() ) {
			return $this->newSuccessResponse();
		}

		return $this->newErrorResponse( $subscriptionResponse );
	}

	private function newSuccessResponse(): array {
		return [ 'status' => 'OK' ];
	}

	private function newErrorResponse( ValidationResponse $response ): array {
		$errors = [];
		/** @var ConstraintViolation $constraintViolation */
		foreach( $response->getValidationErrors() as $constraintViolation ) {
			// TODO add translation library and translate message.
			$errors[$constraintViolation->getSource()] = $constraintViolation->getMessage();
		}
		return [ 'status' => 'ERR', 'errors' => $errors ];
	}
}