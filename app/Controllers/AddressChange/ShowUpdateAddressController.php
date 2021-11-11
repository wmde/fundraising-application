<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\App\Controllers\AddressChange;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use WMDE\Fundraising\Frontend\App\AccessDeniedException;
use WMDE\Fundraising\Frontend\App\Routes;
use WMDE\Fundraising\Frontend\Factories\FunFunFactory;

/**
 * @license GPL-2.0-or-later
 */
class ShowUpdateAddressController {

	public function index( Request $request, FunFunFactory $ffFactory ): Response {
		$addressToken = $request->get( 'addressToken', '' );
		if ( $addressToken === '' ) {
			throw new AccessDeniedException( 'address_change_no_token_in_request' );
		}

		$addressChangeRepository = $ffFactory->getAddressChangeRepository();
		$addressChange = $addressChangeRepository->getAddressChangeByUuid( $addressToken );
		if ( $addressChange === null ) {
			$ffFactory->getLogger()->notice( 'Address change record not found', [ 'addressChangeToken' => $addressToken ] );
			throw new AccessDeniedException( 'address_change_token_not_found' );
		}
		return new Response(
			$ffFactory->getLayoutTemplate( 'Update_Address.html.twig' )->render(
				[
					'addressToken' => $addressToken,
					'isCompany' => $addressChange->isCompanyAddress(),
					'countries' => $ffFactory->getCountries(),
					'addressValidationPatterns' => $ffFactory->getValidationRules()->address,
					'urls' => array_merge(
						Routes::getNamedRouteUrls( $ffFactory->getUrlGenerator() ),
						[
							'updateAddress' => $ffFactory->getUrlGenerator()->generateAbsoluteUrl( Routes::UPDATE_ADDRESS, [ 'addressToken' => $addressToken ] )
						]
					)
				]
			)
		);
	}
}
