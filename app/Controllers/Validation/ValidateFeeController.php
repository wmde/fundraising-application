<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\App\Controllers\Validation;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use UnexpectedValueException;
use WMDE\Euro\Euro;
use WMDE\Fundraising\Frontend\Factories\FunFunFactory;
use WMDE\Fundraising\MembershipContext\UseCases\ValidateMembershipFee\ValidateMembershipFeeUseCase;

class ValidateFeeController {

	public function index( Request $httpRequest, FunFunFactory $factory ): Response {
		try {
			$fee = $this->euroFromRequest( $httpRequest );
		}
		catch ( UnexpectedValueException $ex ) {
			return $this->newJsonErrorResponse( 'not-money' );
		}

		$response = ( new ValidateMembershipFeeUseCase( $factory->newPaymentServiceFactory() ) )->validate(
			$fee->getEuros(),
			(int)$httpRequest->request->get( 'paymentIntervalInMonths', '0' ),
			$httpRequest->request->get( 'addressType', '' ),
			$httpRequest->request->get( 'paymentType', '' )
		);

		if ( $response->isSuccessful() ) {
			return new JsonResponse( [ 'status' => 'OK' ] );
		}

		return $this->newJsonErrorResponse( $response->getValidationErrors()[0]->getMessageIdentifier() );
	}

	private function euroFromRequest( Request $httpRequest ): Euro {
		$currentFeeString = $httpRequest->request->get( 'membershipFee', '' );
		if ( !ctype_digit( $currentFeeString ) ) {
			throw new UnexpectedValueException();
		}
		return Euro::newFromCents(
			intval( $currentFeeString )
		);
	}

	private function newJsonErrorResponse( string $errorCode ): Response {
		return new JsonResponse( [
			'status' => 'ERR',
			'messages' => [
				'membershipFee' => $errorCode
			]
		] );
	}

}
