<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\App\Controllers\Validation;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use WMDE\Euro\Euro;
use WMDE\Fundraising\Frontend\Factories\FunFunFactory;

/**
 * @license GPL-2.0-or-later
 */
class ValidateDonationAmountController {

	public function validate( Request $request, FunFunFactory $ffFactory ): Response {
		$rawAmount = $request->request->get( 'amount', '' );

		if ( !ctype_digit( $rawAmount ) ) {
			return new JsonResponse( [ 'status' => 'ERR', 'messages' => [ 'amount' => 'Amount must be in cents.' ] ] );
		}
		$violations = $ffFactory->newPaymentDataValidator()->validateAmount( Euro::newFromCents( (int)$rawAmount ) );

		if ( $violations != null ) {
			return new JsonResponse( [ 'status' => 'ERR', 'messages' => [ 'amount' => $violations->getMessageIdentifier() ] ] );
		}

		return new JsonResponse( [ 'status' => 'OK' ] );
	}
}
