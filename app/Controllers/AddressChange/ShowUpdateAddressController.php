<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\App\Controllers\AddressChange;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use WMDE\Fundraising\AddressChangeContext\Domain\Model\AddressChange;
use WMDE\Fundraising\Frontend\App\AccessDeniedException;
use WMDE\Fundraising\Frontend\App\Routes;
use WMDE\Fundraising\Frontend\Factories\FunFunFactory;

class ShowUpdateAddressController {

	public function index( Request $request, FunFunFactory $ffFactory ): Response {
		$addressToken = (string)$request->get( 'addressToken', '' );
		if ( $addressToken === '' ) {
			throw new AccessDeniedException( 'address_change_no_token_in_request' );
		}

		$addressChangeRepository = $ffFactory->getAddressChangeRepository();
		$addressChange = $addressChangeRepository->getAddressChangeByUuids( $addressToken, $addressToken );
		if ( $addressChange === null ) {
			$ffFactory->getLogger()->notice( 'Address change record not found', [ 'addressChangeToken' => $addressToken ] );
			throw new AccessDeniedException( 'address_change_token_not_found' );
		}

		if ( $this->addressChangeIsAlreadyUpdated( $addressToken, $addressChange ) ) {
			$url = $ffFactory->getUrlGenerator()->generateAbsoluteUrl(
				Routes::UPDATE_ADDRESS_ALREADY_UPDATED,
				[ 'addressToken' => $addressToken ]
			);
			return new RedirectResponse( $url );
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
							'updateAddress' => $ffFactory->getUrlGenerator()->generateAbsoluteUrl( Routes::UPDATE_ADDRESS_PUT, [ 'identifier' => $addressToken ] )
						]
					)
				]
			)
		);
	}

	private function addressChangeIsAlreadyUpdated( string $addressToken, AddressChange $addressChange ): bool {
		return $addressChange->hasBeenUsed() && $addressChange->getPreviousIdentifier()->equals( $addressToken );
	}
}
