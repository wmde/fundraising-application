<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Presentation\Presenters;

use WMDE\Fundraising\Frontend\Presentation\TwigTemplate;

/**
 * Render an error page
 */
class InternalErrorHtmlPresenter implements ExceptionHtmlPresenterInterface {

	private TwigTemplate $template;

	public function setTemplate( TwigTemplate $template ): void {
		$this->template = $template;
	}

	public function present( \Throwable $exception ): string {
		return $this->template->render( [] );
	}
}
