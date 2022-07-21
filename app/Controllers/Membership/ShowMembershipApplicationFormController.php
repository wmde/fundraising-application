<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\App\Controllers\Membership;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use WMDE\Fundraising\DonationContext\UseCases\GetDonation\GetDonationRequest;
use WMDE\Fundraising\Frontend\App\Routes;
use WMDE\Fundraising\Frontend\Factories\FunFunFactory;
use WMDE\Fundraising\Frontend\Presentation\DonationMembershipApplicationAdapter;

class ShowMembershipApplicationFormController {

	public function index( FunFunFactory $ffFactory, Request $httpRequest ): Response {
		$urls = Routes::getNamedRouteUrls( $ffFactory->getUrlGenerator() );
		$showMembershipTypeOption = $httpRequest->query->get( 'type' ) !== 'sustaining';

		$useCase = $ffFactory->newGetDonationUseCase( $httpRequest->query->get( 'donationAccessToken', '' ) );
		$responseModel = $useCase->showConfirmation(
			new GetDonationRequest(
				$httpRequest->query->getInt( 'donationId' )
			)
		);

		$donation = $responseModel->getDonation();

		$initialDonationFormValues = [];
		$initialDonationValidationResult = [];
		if ( $responseModel->accessIsPermitted() ) {
			$payment = $ffFactory->newPaymentRepository()->getPaymentById( $donation->getPaymentId() );
			$adapter = new DonationMembershipApplicationAdapter( $ffFactory->newBankDataConverter() );
			$initialDonationFormValues = $adapter->getInitialMembershipFormValues( $donation, $payment );
			$initialDonationValidationResult = $adapter->getInitialValidationState( $donation, $payment );
		}

		return new Response( $ffFactory->newMembershipApplicationFormPresenter()->present(
			$urls,
			$showMembershipTypeOption,
			$initialDonationFormValues,
			$initialDonationValidationResult
		) );
	}
}
