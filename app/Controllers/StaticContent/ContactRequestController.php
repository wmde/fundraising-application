<?php

namespace WMDE\Fundraising\Frontend\App\Controllers\StaticContent;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use WMDE\Fundraising\Frontend\Factories\FunFunFactory;
use WMDE\Fundraising\Frontend\UseCases\GetInTouch\GetInTouchRequest;

class ContactRequestController {

	public function index( FunFunFactory $ffFactory, Request $request ): Response {
		$contactFormRequest = new GetInTouchRequest(
			$request->request->get( 'firstname', '' ),
			$request->request->get( 'lastname', '' ),
			$request->request->get( 'email', '' ),
			$request->request->get( 'donationNumber', '' ),
			$request->request->get( 'subject', '' ),
			$request->request->get( 'category', '' ),
			$request->request->get( 'messageBody', '' )
		);

		$contactFormResponse = $ffFactory->newGetInTouchUseCase()->processContactRequest( $contactFormRequest );
		if ( $contactFormResponse->isSuccessful() ) {
			return new RedirectResponse(
				$ffFactory->getUrlGenerator()->generateRelativeUrl( 'page', [ 'pageName' => 'Kontakt_Bestaetigung' ] )
			);
		}

		return new Response( $ffFactory->newGetInTouchHtmlPresenter()->present(
			$contactFormResponse,
			$request->request->all()
		) );
	}
}
