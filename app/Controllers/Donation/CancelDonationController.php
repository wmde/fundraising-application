<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\App\Controllers\Donation;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use WMDE\Fundraising\DonationContext\UseCases\CancelDonation\CancelDonationRequest;
use WMDE\Fundraising\Frontend\Factories\FunFunFactory;

class CancelDonationController {

	public function index( FunFunFactory $ffFactory, Request $request ): Response {
		$cancellationRequest = new CancelDonationRequest(
			(int)$request->request->get( 'sid', '' )
		);

		$responseModel = $ffFactory->newCancelDonationUseCase( $request->request->get( 'utoken', '' ) )
			->cancelDonation( $cancellationRequest );

		$httpResponse = new Response( $ffFactory->newCancelDonationHtmlPresenter()->present( $responseModel ) );
		if ( $responseModel->cancellationSucceeded() ) {
			$httpResponse->headers->clearCookie( 'donation_timestamp' );
		}

		return $httpResponse;
	}
}
