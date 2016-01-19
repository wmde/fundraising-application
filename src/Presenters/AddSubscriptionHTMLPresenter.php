<?php

namespace WMDE\Fundraising\Frontend\Presenters;

use WMDE\Fundraising\Frontend\ResponseModel\AddSubscriptionResponse;

/**
 * @licence GNU GPL v2+
 * @author Gabriel Birke < gabriel.birke@wikimedia.de >
 */
class AddSubscriptionHTMLPresenter {



	public function present( AddSubscriptionResponse $subscriptionResponse ) {
		if ( $subscriptionResponse->isSuccessful() ) {
			return $this->newSuccessResponse();
		}

		return $this->newErrorResponse( $subscriptionResponse );
	}

	private function newSuccessResponse() {
		// TODO Render success page/template
		return 'TODO';
	}

	private function newErrorResponse( AddSubscriptionResponse $response ) {
		// TODO: When https://github.com/wmde/FundraisingFrontend/pull/41 is merged,
		// render form with values from HTTP request and $response->getValidationErrors()
		return 'todo';
	}
}