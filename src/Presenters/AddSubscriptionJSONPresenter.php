<?php

namespace WMDE\Fundraising\Frontend\Presenters;

use WMDE\Fundraising\Frontend\ResponseModel\AddSubscriptionResponse;

/**
 * @licence GNU GPL v2+
 * @author Gabriel Birke < gabriel.birke@wikimedia.de >
 */
class AddSubscriptionJSONPresenter {

	public function present( AddSubscriptionResponse $subscriptionResponse ) {
		if ( $subscriptionResponse->isSuccessful() ) {
			return $this->newSuccessResponse();
		}

		return $this->newErrorResponse( $subscriptionResponse );
	}

	private function newSuccessResponse() {
		return [ 'status' => 'OK' ];
	}

	private function newErrorResponse( AddSubscriptionResponse $response ) {
		$errors = [];
		// TODO: When https://github.com/wmde/FundraisingFrontend/pull/41 is merged, fill $errors with
		// translated strings generated from $response->getValidationErrors().
		// The field names from $response->getValidationErrors() should be the field names in $errors
		return [ 'status' => 'ERR' ];
	}
}