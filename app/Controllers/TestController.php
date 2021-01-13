<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\App\Controllers;

use Symfony\Component\HttpFoundation\Response;
use WMDE\Fundraising\Frontend\Factories\FunFunFactory;

class TestController {

	public function handle(FunFunFactory $ffFactory): Response {
		return new Response( sprintf(
			'<html><head><title>Test Controller</title></head>'.
				'<body>Something here: %s</body></html>',
			$ffFactory->getCachePath()
		) );
	}
}
