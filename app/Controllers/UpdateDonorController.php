<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\App\Controllers;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use WMDE\Fundraising\DonationContext\UseCases\UpdateDonor\UpdateDonorRequest;
use WMDE\Fundraising\Frontend\Factories\FunFunFactory;

/**
 * @license GNU GPL v2+
 */
class UpdateDonorController {

	public function updateDonor( Request $request, FunFunFactory $ffFactory ): Response {
		$updateToken = $request->request->get( 'updateToken', '' );
		$accessToken = $request->query->get( 'accessToken', '' );
		$responseModel = $ffFactory
			->newUpdateDonorUseCase( $updateToken, $accessToken )
			->updateDonor( $this->newRequestModel( $request ) );

		return new Response(
			$ffFactory->newDonorUpdatePresenter()->present( $responseModel, $updateToken, $accessToken )
		);

	}

	private function newRequestModel( Request $request ): UpdateDonorRequest {
		return UpdateDonorRequest::newInstance()
			->withDonationId( intval( $request->get( 'donation_id', '' ) ) )
			->withCity( $request->get( 'city', '' ) )
			->withCompanyName( $request->get( 'companyName', '' ) )
			->withCountryCode( $request->get( 'country', '' ) )
			->withEmailAddress( $request->get( 'email', '' ) )
			->withFirstName( $request->get( 'firstName', '' ) )
			->withLastName( $request->get( 'lastName', '' ) )
			->withPostalCode( $request->get( 'postcode', '' ) )
			->withSalutation( $request->get( 'salutation', '' ) )
			->withStreetAddress( $request->get( 'street', '' ) )
			->withTitle( $request->get( 'title', '' ) )
			->withType( $request->get( 'addressType', '' ) );
	}
}