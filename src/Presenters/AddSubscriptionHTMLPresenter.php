<?php

namespace WMDE\Fundraising\Frontend\Presenters;

use WMDE\Fundraising\Frontend\ResponseModel\ValidationResponse;
use WMDE\Fundraising\Frontend\TwigTemplate;
use WMDE\Fundraising\Frontend\Validation\ConstraintViolation;

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

	public function present( ValidationResponse $subscriptionResponse, array $formData ): string {
		$errors = [];
		/** @var ConstraintViolation $constraintViolation */
		foreach( $subscriptionResponse->getValidationErrors() as $constraintViolation ) {
			// TODO add translation library and translate message.
			$errors[] = $constraintViolation->getMessage();
		}
		return $this->template->render( array_merge( $formData, [ 'errors' => $errors ] ) );
	}


}