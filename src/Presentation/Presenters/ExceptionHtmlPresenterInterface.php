<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Presentation\Presenters;

use WMDE\Fundraising\Frontend\Presentation\TwigTemplate;

interface ExceptionHtmlPresenterInterface {
	public function setTemplate( TwigTemplate $template ): void;

	public function present( \Throwable $exception ): string;
}
