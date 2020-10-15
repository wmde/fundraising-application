<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\App\Controllers\Validation;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use WMDE\Fundraising\Frontend\Factories\FunFunFactory;

/**
 * @license GPL-2.0-or-later
 */
class ValidationController {

	public function validateEmail( Request $request, FunFunFactory $ffFactory ): Response {
		$validationResult = $ffFactory->getEmailValidator()->validate( $request->request->get( 'email', '' ) );
		return new JsonResponse( [
			'status' => $validationResult->isSuccessful() ? 'OK' : 'ERR'
		] );
	}

}
