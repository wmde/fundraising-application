<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\App\Controllers;

use Symfony\Component\HttpFoundation\Response;
use WMDE\Fundraising\Frontend\App\AccessDeniedException;
use WMDE\Fundraising\Frontend\Factories\FunFunFactory;

/**
 * @license GNU GPL v2+
 */
class ShowUpdateAddressController {

	public const ADDRESS_CHANGE_SESSION_KEY = 'address_changed';

	public function showForm( string $addressToken, FunFunFactory $ffFactory ): Response {
		$addressChangeRepository = $ffFactory->newAddressChangeRepository();
		$addressChange = $addressChangeRepository->getAddressChangeByUuid( $addressToken );
		if ( $addressChange === null ) {
			throw new AccessDeniedException();
		}
		return new Response(
			$ffFactory->getLayoutTemplate( 'Update_Address.html.twig' )->render(
				[
					'addressToken' => $addressToken,
					'isCompany' => $addressChange->isCompanyAddress(),
					'messages' => $ffFactory->getMessages()
				]
			)
		);
	}
}