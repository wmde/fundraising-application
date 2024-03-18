<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Presentation\Presenters;

use WMDE\Fundraising\Frontend\Presentation\TwigTemplate;
use WMDE\FunValidators\ValidationResponse;

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
