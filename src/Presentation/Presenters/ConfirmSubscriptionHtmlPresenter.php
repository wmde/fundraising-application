<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Presentation\Presenters;

use WMDE\Fundraising\Frontend\Presentation\TwigTemplate;
use WMDE\FunValidators\ValidationResponse;

/**
 * @license GPL-2.0-or-later
 * @author Gabriel Birke < gabriel.birke@wikimedia.de >
 * @author Kai Nissen < kai.nissen@wikimedia.de >
 */
class ConfirmSubscriptionHtmlPresenter {

	private TwigTemplate $template;

	public function __construct( TwigTemplate $template ) {
		$this->template = $template;
	}

	public function present( ValidationResponse $confirmationResponse ): string {
		$contextVariables = [];
		if ( !$confirmationResponse->isSuccessful() ) {
			$contextVariables['error_message'] = $confirmationResponse->getValidationErrors()[0]->getMessageIdentifier();
		}
		return $this->template->render( $contextVariables );
	}
}
