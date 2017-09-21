<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\App\RouteHandlers;

use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use WMDE\Fundraising\Frontend\App\AccessDeniedException;
use WMDE\Fundraising\Frontend\DonationContext\UseCases\ShowDonationConfirmation\ShowDonationConfirmationRequest;
use WMDE\Fundraising\Frontend\Factories\FunFunFactory;
use WMDE\Fundraising\Frontend\Infrastructure\PiwikVariableCollector;

/**
 * @license GNU GPL v2+
 * @author Kai Nissen < kai.nissen@wikimedia.de >
 */
class ShowDonationConfirmationHandler {

	const SUBMISSION_COOKIE_NAME = 'donation_timestamp';
	const TIMESTAMP_FORMAT = 'Y-m-d H:i:s';

	private $ffFactory;

	public function __construct( FunFunFactory $ffFactory ) {
		$this->ffFactory = $ffFactory;
	}

	public function handle( Request $request, array $sessionTrackingData ): Response {
		$useCase = $this->ffFactory->newShowDonationConfirmationUseCase( $request->get( 'accessToken', '' ) );

		$responseModel = $useCase->showConfirmation( new ShowDonationConfirmationRequest(
			(int)$request->get( 'id', '' )
		) );

		if ( !$responseModel->accessIsPermitted() ) {
			throw new AccessDeniedException( 'access_denied_donation_confirmation' );
		}

		$selectedConfirmationPage = $this->ffFactory->getDonationConfirmationPageSelector()->selectPage();
		$httpResponse = new Response(
			$this->ffFactory->newDonationConfirmationPresenter()->present(
				$responseModel->getDonation(),
				$responseModel->getUpdateToken(),
				$selectedConfirmationPage,
				PiwikVariableCollector::newForDonation( $sessionTrackingData, $responseModel->getDonation() )
			)
		);

		if ( !$request->cookies->get( self::SUBMISSION_COOKIE_NAME ) ) {
			$cookie = $this->ffFactory->getCookieBuilder();
			$httpResponse->headers->setCookie(
				$cookie->newCookie( self::SUBMISSION_COOKIE_NAME, date( self::TIMESTAMP_FORMAT ) )
			);
		}
		return $httpResponse;
	}

}