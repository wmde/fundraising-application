<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Presentation\Presenters;

use WMDE\Fundraising\Frontend\Presentation\TwigTemplate;
use WMDE\FunValidators\ConstraintViolation;
use WMDE\FunValidators\ValidationResponse;

/**
 * Render the subscription HTML form with errors
 */
class AddSubscriptionHtmlPresenter {

	public function __construct( private readonly TwigTemplate $template ) {
	}

	public function present( ValidationResponse $subscriptionResponse, array $formData ): string {
		$errors = [];
		/** @var ConstraintViolation $constraintViolation */
		foreach ( $subscriptionResponse->getValidationErrors() as $constraintViolation ) {
			$errors[$constraintViolation->getSource()] = $constraintViolation->getMessageIdentifier();
		}
		return $this->template->render( array_merge( $formData, [ 'errors' => $errors ] ) );
	}

}
