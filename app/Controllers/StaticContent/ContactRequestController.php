<?php

namespace WMDE\Fundraising\Frontend\App\Controllers\StaticContent;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use WMDE\Fundraising\Frontend\Factories\FunFunFactory;
use WMDE\Fundraising\Frontend\UseCases\GetInTouch\GetInTouchRequest;

class ContactRequestController {

	public function handle( FunFunFactory $ffFactory, Request $request ) {
		$contactFormRequest = new GetInTouchRequest(
			$request->get( 'firstname', '' ),
			$request->get( 'lastname', '' ),
			$request->get( 'email', '' ),
			$request->get( 'donationNumber', '' ),
			$request->get( 'subject', '' ),
			$request->get( 'category', '' ),
			$request->get( 'messageBody', '' )
		);

		$contactFormResponse = $ffFactory->newGetInTouchUseCase()->processContactRequest( $contactFormRequest );
		if ( $contactFormResponse->isSuccessful() ) {
			return new RedirectResponse(
				$ffFactory->getUrlGenerator()->generateRelativeUrl( 'page', [ 'pageName' => 'Kontakt_Bestaetigung' ] )
			);
		}

		return $ffFactory->newGetInTouchHtmlPresenter()->present(
			$contactFormResponse,
			$request->request->all()
		);
	}
}
