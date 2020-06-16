<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\App\Controllers;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use WMDE\Fundraising\Frontend\Factories\FunFunFactory;

/**
 * @license GNU GPL v2+
 */
class ValidationController {

	public function validateEmail( Request $request, FunFunFactory $ffFactory ): Response {
		$validationResult = $ffFactory->getEmailValidator()->validate( $request->request->get( 'email', '' ) );
		return new JsonResponse( [
			'status' => $validationResult->isSuccessful() ? 'OK' : 'ERR'
		] );
	}

}