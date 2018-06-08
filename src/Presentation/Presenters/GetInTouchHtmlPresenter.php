<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Presentation\Presenters;

use Symfony\Component\Translation\TranslatorInterface;
use WMDE\Fundraising\Frontend\Presentation\TwigTemplate;
use WMDE\FunValidators\ValidationResponse;
use WMDE\FunValidators\ConstraintViolation;

/**
 * Render the contact form with errors
 *
 * @licence GNU GPL v2+
 * @author Kai Nissen < kai.nissen@wikimedia.de >
 */
class GetInTouchHtmlPresenter {

	private $template;
	private $translator;
	private $categories;

	public function __construct( TwigTemplate $template, TranslatorInterface $translator, array $categories ) {
		$this->template = $template;
		$this->translator = $translator;
		$this->categories = $categories;
	}

	public function present( ValidationResponse $response, array $formData ): string {
		$errors = [];
		/** @var ConstraintViolation $constraintViolation */
		foreach ( $response->getValidationErrors() as $constraintViolation ) {
			$errors[$constraintViolation->getSource()] = $this->translator->trans( $constraintViolation->getMessageIdentifier() );
		}

		return $this->template->render( array_merge( $formData, [ 'errors' => $errors ], [ 'contact_categories' => $this->categories ] ) );
	}

}
