<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\App\Controllers\Validation;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use WMDE\Fundraising\Frontend\Factories\FunFunFactory;

class FindStreetsController {

	public function index( FunFunFactory $ffFactory, Request $request ): Response {
		$streets = $ffFactory->newFindStreetsUseCase()
			->getStreetsForPostcode( $request->get( 'postcode', '' ) );

		return new JsonResponse( $streets );
	}
}
