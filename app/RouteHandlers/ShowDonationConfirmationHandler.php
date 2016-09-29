<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\App\RouteHandlers;

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

	private $ffFactory;

	public function __construct( FunFunFactory $ffFactory ) {
		$this->ffFactory = $ffFactory;
	}

	public function handle( Request $request, array $sessionTrackingData ): Response {
		$useCase = $this->ffFactory->newShowDonationConfirmationUseCase( $request->get( 'accessToken', '' ) );

		$responseModel = $useCase->showConfirmation( new ShowDonationConfirmationRequest(
			(int)$request->get( 'donationId', '' )
		) );

		if ( $responseModel->accessIsPermitted() ) {
			$selectedConfirmationPage = $this->ffFactory->getDonationConfirmationPageSelector()->selectPage();
			return new Response(
				$this->ffFactory->newDonationConfirmationPresenter()->present(
					$responseModel->getDonation(),
					$responseModel->getUpdateToken(),
					$selectedConfirmationPage,
					PiwikVariableCollector::newForDonation( $sessionTrackingData, $responseModel->getDonation() )
				)
			);
		}

		throw new AccessDeniedException( 'access_denied_donation_confirmation' );
	}

}