<?php

declare(strict_types = 1);

namespace WMDE\Fundraising\Frontend\Presentation\Presenters;

use WMDE\Fundraising\Frontend\TwigTemplate;
use WMDE\Fundraising\Frontend\ResponseModel\ValidationResponse;

/**
 * @license GNU GPL v2+
 * @author Gabriel Birke < gabriel.birke@wikimedia.de >
 */
class ConfirmSubscriptionHtmlPresenter {
	private $template;

	public function __construct( TwigTemplate $template ) {
		$this->template = $template;
	}

	public function present( ValidationResponse $confirmationResponse ): string {
		$contextVariables = [];
		if ( ! $confirmationResponse->isSuccessful() ) {
			$contextVariables['error_message'] = $confirmationResponse->getValidationErrors()[0]->getMessage();
		}
		return $this->template->render( $contextVariables );
	}
}