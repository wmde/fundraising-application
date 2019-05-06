<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\App\Controllers;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use WMDE\Euro\Euro;
use WMDE\Fundraising\Frontend\Factories\FunFunFactory;

/**
 * @license GNU GPL v2+
 */
class ValidateDonationAmountController {

	public function validate( Request $request, FunFunFactory $ffFactory ): Response {
		$rawAmount = $request->request->get( 'amount', '' );

		if( !ctype_digit( $rawAmount ) ) {
			return new JsonResponse( [ 'status' => 'ERR', 'messages' => ['amount' => 'Amount must be in cents.'] ] );
		}
		$violations = $ffFactory->newPaymentDataValidator()->validateAmount( Euro::newFromCents( (int)$rawAmount ) );

		if ( $violations != null ) {
			return new JsonResponse( [ 'status' => 'ERR', 'messages' => ['amount' => $violations->getMessageIdentifier()] ] );
		}

		return new JsonResponse( [ 'status' => 'OK' ] );
	}
}