<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\App\Controllers\Validation;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use WMDE\Fundraising\Frontend\Factories\FunFunFactory;

class FindCitiesController {

	public function index( FunFunFactory $ffFactory, Request $request ): Response {
		$cities = $ffFactory->newFindCitiesUseCase()
			->getCitiesForPostcode( $request->get( 'postcode', '' ) );

		return new JsonResponse( array_map( "utf8_encode", $cities ) );
	}
}
