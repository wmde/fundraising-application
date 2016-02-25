<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Presentation\Presenters;

use Symfony\Component\Translation\TranslatorInterface;
use WMDE\Fundraising\Frontend\ResponseModel\ValidationResponse;
use WMDE\Fundraising\Frontend\TwigTemplate;
use WMDE\Fundraising\Frontend\Validation\ConstraintViolation;

/**
 * Render the contact form with errors
 *
 * @licence GNU GPL v2+
 * @author Kai Nissen < kai.nissen@wikimedia.de >
 */
class GetInTouchHTMLPresenter {

	private $template;
	private $translator;

	public function __construct( TwigTemplate $template, TranslatorInterface $translator ) {
		$this->template = $template;
		$this->translator = $translator;
	}

	public function present( ValidationResponse $response, array $formData ): string {
		$errors = [];
		/** @var ConstraintViolation $constraintViolation */
		foreach ( $response->getValidationErrors() as $constraintViolation ) {
			$errors[$constraintViolation->getSource()] = $this->translator->trans( $constraintViolation->getMessageIdentifier() );
		}

		return $this->template->render( array_merge( $formData, [ 'errors' => $errors ] ) );
	}

}
