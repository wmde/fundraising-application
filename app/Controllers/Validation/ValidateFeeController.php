<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\App\Controllers\Validation;

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
		ValidateFeeResult::ERROR_INTERVAL_INVALID => 'interval-invalid',
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

	private function euroFromRequest( Request $httpRequest ): Euro {
		$currentFeeString = $httpRequest->request->get( 'membershipFee', '' );
		if ( !ctype_digit( $currentFeeString ) ) {
			throw new InvalidArgumentException();
		}
		return Euro::newFromCents(
			intval( $currentFeeString )
		);
	}

	private function newJsonErrorResponse( string $errorCode ): Response {
		if ( empty( self::ERROR_RESPONSE_MAP[$errorCode] ) ) {
			throw new \OutOfBoundsException( 'Validation error code not found' );
		}
		return new JsonResponse( [
			'status' => 'ERR',
			'messages' => [
				'membershipFee' => self::ERROR_RESPONSE_MAP[$errorCode]
			]
		] );
	}

}
