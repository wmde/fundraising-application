<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\App\Controllers;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class TestController {

	public function handle( Request $request ): Response {
		return new Response( sprintf('Something here: %s', $request->getBaseUrl()) );
	}
}
