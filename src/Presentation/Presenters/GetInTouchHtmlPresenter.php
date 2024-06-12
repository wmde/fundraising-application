<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Presentation\Presenters;

use WMDE\Fundraising\Frontend\Presentation\TwigTemplate;
use WMDE\FunValidators\ConstraintViolation;
use WMDE\FunValidators\ValidationResponse;

/**
 * Render the contact form with errors
 */
class GetInTouchHtmlPresenter {

	/**
	 * @param TwigTemplate $template
	 * @param string[] $categories
	 */
	public function __construct(
		private readonly TwigTemplate $template,
		private readonly array $categories
	) {
	}

	/**
	 * @param ValidationResponse $response
	 * @param array<string, mixed> $formData
	 */
	public function present( ValidationResponse $response, array $formData ): string {
		$errors = [];
		/** @var ConstraintViolation $constraintViolation */
		foreach ( $response->getValidationErrors() as $constraintViolation ) {
			$errors[$constraintViolation->getSource()] = $constraintViolation->getMessageIdentifier();
		}

		return $this->template->render( array_merge( [ 'submitted_form_data' => $formData ], [ 'errors' => $errors ], [ 'contact_categories' => $this->categories ] ) );
	}

}
