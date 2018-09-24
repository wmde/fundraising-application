<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\App\Controllers;

use InvalidArgumentException;
use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use WMDE\Euro\Euro;
use WMDE\Fundraising\MembershipContext\UseCases\ValidateMembershipFee\ValidateFeeRequest;
use WMDE\Fundraising\MembershipContext\UseCases\ValidateMembershipFee\ValidateMembershipFeeUseCase;

class ValidateFeeController {

	private $app;
	private $httpRequest;

	public function __construct( Application $app, Request $httpRequest ) {
		$this->app = $app;
		$this->httpRequest = $httpRequest;
	}

	public function validateFee(): Response {
		try {
			$fee = Euro::newFromString(
				str_replace( ',', '.', $this->httpRequest->request->get( 'amount', '' ) )
			);
		}
		catch ( InvalidArgumentException $ex ) {
			return $this->app->json( [
				'status' => 'ERR',
				'messages' => [
					'amount' => 'not-money'
				]
			] );
		}

		$request = ValidateFeeRequest::newInstance()
			->withFee( $fee )
			->withInterval( (int)$this->httpRequest->request->get( 'paymentIntervalInMonths', '0' ) )
			->withApplicantType( $this->httpRequest->request->get( 'addressType', '' ) );

		$response = ( new ValidateMembershipFeeUseCase() )->validate( $request );


		if ( $response->isSuccessful() ) {
			return $this->app->json( [ 'status' => 'OK' ] );
		}

		return $this->app->json( [
			'status' => 'ERR',
			'messages' => [
				'amount' => 'too-low'
			]
		] );
	}

}