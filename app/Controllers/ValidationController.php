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

	public function validateDonationPayment( Request $request, FunFunFactory $ffFactory ): Response {
		$amount = (float) $ffFactory->newDecimalNumberFormatter()->parse( $request->get( 'amount', '0' ) );
		$validator = $ffFactory->newPaymentDataValidator();
		$validationResult = $validator->validate( $amount, (string) $request->get( 'paymentType', '' ) );

		if ( $validationResult->isSuccessful() ) {
			return new JsonResponse( [ 'status' => 'OK' ] );
		}

		$errors = [];
		foreach( $validationResult->getViolations() as $violation ) {
			$errors[$violation->getSource()] = $ffFactory->getTranslator()->trans( $violation->getMessageIdentifier() );
		}
		return new JsonResponse( [ 'status' => 'ERR', 'messages' => $errors ] );

	}
}