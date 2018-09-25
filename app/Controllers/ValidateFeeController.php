<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\App\Controllers;

use InvalidArgumentException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use WMDE\Euro\Euro;
use WMDE\Fundraising\MembershipContext\UseCases\ValidateMembershipFee\ValidateFeeRequest;
use WMDE\Fundraising\MembershipContext\UseCases\ValidateMembershipFee\ValidateFeeResult;
use WMDE\Fundraising\MembershipContext\UseCases\ValidateMembershipFee\ValidateMembershipFeeUseCase;

class ValidateFeeController {

	private const ERROR_RESPONSE_MAP = [
		ValidateFeeResult::ERROR_TOO_LOW => 'too-low',
		'not-money' => 'not-money'
	];

	public function validateFee( Request $httpRequest ): Response {
		try {
			$fee = $this->euroFromRequest( $httpRequest );
		}
		catch ( InvalidArgumentException $ex ) {
			return $this->newJsonErrorResponse( 'not-money' );
		}

		$request = ValidateFeeRequest::newInstance()
			->withFee( $fee )
			->withInterval( (int)$httpRequest->request->get( 'paymentIntervalInMonths', '0' ) )
			->withApplicantType( $httpRequest->request->get( 'addressType', '' ) );

		$response = ( new ValidateMembershipFeeUseCase() )->validate( $request );

		if ( $response->isSuccessful() ) {
			return new JsonResponse( [ 'status' => 'OK' ] );
		}

		return $this->newJsonErrorResponse( $response->getErrorCode() );
	}

	/**
	 * @throws InvalidArgumentException
	 */
	private function euroFromRequest( Request $httpRequest ): Euro {
		return Euro::newFromString(
			str_replace( ',', '.', $httpRequest->request->get( 'amount', '' ) )
		);
	}

	private function newJsonErrorResponse( string $errorCode ): Response {
		if ( empty( self::ERROR_RESPONSE_MAP[$errorCode] ) ) {
			throw new \OutOfBoundsException( 'Validation error code not found' );
		}
		return new JsonResponse( [
			'status' => 'ERR',
			'messages' => [
				'amount' => self::ERROR_RESPONSE_MAP[$errorCode]
			]
		] );
	}

}