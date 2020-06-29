<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Presentation\Presenters;

use WMDE\Fundraising\Frontend\Presentation\TwigTemplate;
use WMDE\FunValidators\ConstraintViolation;
use WMDE\FunValidators\ValidationResponse;

/**
 * Render the subscription HTML form with errors
 *
 * @license GPL-2.0-or-later
 * @author Gabriel Birke < gabriel.birke@wikimedia.de >
 * @author Kai Nissen < kai.nissen@wikimedia.de >
 */
class AddSubscriptionHtmlPresenter {

	private TwigTemplate $template;

	public function __construct( TwigTemplate $template ) {
		$this->template = $template;
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
