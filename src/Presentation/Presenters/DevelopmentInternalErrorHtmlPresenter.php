<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Presentation\Presenters;

use WMDE\Fundraising\Frontend\Presentation\TwigTemplate;

class DevelopmentInternalErrorHtmlPresenter implements ExceptionHtmlPresenterInterface {

	private TwigTemplate $template;

	public function setTemplate( TwigTemplate $template ): void {
		$this->template = $template;
	}

	public function present( \Exception $exception ): string {
		return $this->template->render( [
			'message' => $exception->getMessage(),
			'trace' => $exception->getTrace(),
		] );
	}
}
