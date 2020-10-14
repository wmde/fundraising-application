<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\App\Controllers;

use Symfony\Component\HttpFoundation\Response;
use WMDE\Fundraising\Frontend\Factories\FunFunFactory;

class TestController {

	public function handle(FunFunFactory $ffFactory) {
		return new Response('Cache path, for testing: ' . $ffFactory->getCachePath() );
	}
}
