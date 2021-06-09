<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\App\Controllers;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class PageNotFoundController {

	public function index() {
		throw new NotFoundHttpException( "Page not found." );
	}
}
