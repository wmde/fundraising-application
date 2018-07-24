<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\App\Controllers;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use WMDE\Fundraising\Frontend\App\AccessDeniedException;
use WMDE\Fundraising\DonationContext\UseCases\GetDonation\GetDonationRequest;
use WMDE\Fundraising\Frontend\Factories\FunFunFactory;
use WMDE\Fundraising\Frontend\Infrastructure\PiwikVariableCollector;

/**
 * @license GNU GPL v2+
 * @author Kai Nissen < kai.nissen@wikimedia.de >
 */
class ShowDonationConfirmationController {

	const SUBMISSION_COOKIE_NAME = 'donation_timestamp';
	const TIMESTAMP_FORMAT = 'Y-m-d H:i:s';

	public function show( Request $request, FunFunFactory $ffFactory, Application $application ): Response {
		$useCase = $ffFactory->newGetDonationUseCase( $request->get( 'accessToken', '' ) );

		$responseModel = $useCase->showConfirmation( new GetDonationRequest(
			(int)$request->get( 'id', '' )
		) );

		if ( !$responseModel->accessIsPermitted() ) {
			throw new AccessDeniedException( 'access_denied_donation_confirmation' );
		}

		$httpResponse = new Response(
			$ffFactory->newDonationConfirmationPresenter()->present(
				$responseModel->getDonation(),
				$responseModel->getUpdateToken(),
				$request->get( 'accessToken', '' ),
				PiwikVariableCollector::newForDonation( $application['session']->get( 'piwikTracking', [] ), $responseModel->getDonation() )
			)
		);

		if ( !$request->cookies->get( self::SUBMISSION_COOKIE_NAME ) ) {
			$cookie = $ffFactory->getCookieBuilder();
			$httpResponse->headers->setCookie(
				$cookie->newCookie( self::SUBMISSION_COOKIE_NAME, date( self::TIMESTAMP_FORMAT ) )
			);
		}
		return $httpResponse;
	}

}