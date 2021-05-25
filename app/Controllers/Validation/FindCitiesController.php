<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\App\Controllers\Validation;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use WMDE\Fundraising\Frontend\Factories\FunFunFactory;

class FindCitiesController {

	public function index( FunFunFactory $ffFactory, Request $request ): Response {
		return new JsonResponse( [
			'Takeshi\'s Castle',
			'Mushroom Kingdom City',
			'Alabastia',
			'FÜN-Stadt',
			'Ba Sing Se',
			'Satan City',
			'Gotham City',
			'Kleinstes-Kaff-der-Welt',
			'Entenhausen',
		] );
	}
}
