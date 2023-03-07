<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Presentation\Presenters;

use WMDE\Fundraising\Frontend\Presentation\TwigTemplate;

class PageNotFoundPresenter {

	public function __construct( private readonly TwigTemplate $template ) {
	}

	public function present(): string {
		return $this->template->render( [] );
	}

}
