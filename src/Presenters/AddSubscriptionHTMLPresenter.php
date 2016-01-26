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
		$errors = [];
		/** @var ConstraintViolation $constraintViolation */
		foreach( $subscriptionResponse->getValidationErrors() as $constraintViolation ) {
			// TODO add translation library and translate message.
			$errors[] = $constraintViolation->getMessage();
		}
		return $this->template->render( array_merge( $formData, [ 'errors' => $errors ] ) );
	}


}