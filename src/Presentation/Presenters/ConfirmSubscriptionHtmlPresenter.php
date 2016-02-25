<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Presentation\Presenters;

use Symfony\Component\Translation\TranslatorInterface;
use WMDE\Fundraising\Frontend\TwigTemplate;
use WMDE\Fundraising\Frontend\ResponseModel\ValidationResponse;

/**
 * @license GNU GPL v2+
 * @author Gabriel Birke < gabriel.birke@wikimedia.de >
 * @author Kai Nissen < kai.nissen@wikimedia.de >
 */
class ConfirmSubscriptionHtmlPresenter {

	private $template;
	private $translator;

	public function __construct( TwigTemplate $template, TranslatorInterface $translator ) {
		$this->template = $template;
		$this->translator = $translator;
	}

	public function present( ValidationResponse $confirmationResponse ): string {
		$contextVariables = [];
		if ( ! $confirmationResponse->isSuccessful() ) {
			$contextVariables['error_message'] = $this->translator->trans(
				$confirmationResponse->getValidationErrors()[0]->getMessageIdentifier()
			);
		}
		return $this->template->render( $contextVariables );
	}
}