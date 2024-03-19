<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Presentation\Presenters;

use WMDE\Fundraising\Frontend\Presentation\TwigTemplate;

/**
 * Render an error page
 */
class ErrorPageHtmlPresenter {

	public function __construct( private readonly TwigTemplate $template ) {
	}

	public function present( string $errorMessage ): string {
		return $this->template->render( [ 'message' => $errorMessage ] );
	}

}
