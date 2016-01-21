<?php

namespace WMDE\Fundraising\Frontend\Presenters;

use WMDE\Fundraising\Frontend\ResponseModel\AddSubscriptionResponse;
use WMDE\Fundraising\Frontend\TwigTemplate;

/**
 * Render the subscription HTML form with errors
 *
 * @licence GNU GPL v2+
 * @author Gabriel Birke < gabriel.birke@wikimedia.de >
 */
class AddSubscriptionHTMLPresenter {

	private $template;

	public function __construct( TwigTemplate $template ) {
		$this->template = $template;
	}

	public function present( AddSubscriptionResponse $subscriptionResponse, array $formData ): string {
		// TODO: When https://github.com/wmde/FundraisingFrontend/pull/41 is merged,
		// render form with values from HTTP request and $response->getValidationErrors()
		$validationMessages = []; // TODO: use $response->getValidationErrors() to create these (translated)
		return $this->template->render( array_merge( $formData, [ 'errors' => $validationMessages ] ) );
	}


}