<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\App\Controllers\AddressChange;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use WMDE\Fundraising\Frontend\App\AccessDeniedException;
use WMDE\Fundraising\Frontend\Factories\FunFunFactory;

class ShowAddressChangeSuccessController {

	public function index( Request $request, FunFunFactory $ffFactory ): Response {
		$addressToken = $request->get( 'addressToken', '' );
		if ( $addressToken === '' ) {
			throw new AccessDeniedException( 'address_change_no_token_in_request' );
		}

		$addressChangeRepository = $ffFactory->getAddressChangeRepository();
		$addressChange = $addressChangeRepository->getAddressChangeByUuids( $addressToken, $addressToken );

		return new Response( $ffFactory->getLayoutTemplate( 'AddressUpdateSuccess.html.twig' )->render( [
			'message' => $request->attributes->get( 'successMessage' ),
			'addressToken' => $addressToken,
			'receipt' => $addressChange->isOptedIntoDonationReceipt()
		] ) );
	}
}
