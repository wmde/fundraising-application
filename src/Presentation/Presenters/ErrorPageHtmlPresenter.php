<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Presentation\Presenters;

use WMDE\Fundraising\Frontend\Presentation\TwigTemplate;

/**
 * Render an error page
 *
 * @licence GNU GPL v2+
 */
class ErrorPageHtmlPresenter {

	private $template;

	public function __construct( TwigTemplate $template ) {
		$this->template = $template;
	}

	public function present( string $errorMessage ): string {
		return $this->template->render( [ 'message' => $errorMessage ] );
	}

}