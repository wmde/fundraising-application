<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\App\Controllers\Membership;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use WMDE\Fundraising\Frontend\Factories\FunFunFactory;
use WMDE\Fundraising\MembershipContext\UseCases\ShowApplicationConfirmation\ShowAppConfirmationRequest;

class ShowMembershipConfirmationController {

	public function index( FunFunFactory $ffFactory, Request $request ): Response {
		$ffFactory->getTranslationCollector()->addTranslationFile( $ffFactory->getI18nDirectory() . '/messages/paymentTypes.json' );
		$presenter = $ffFactory->newMembershipApplicationConfirmationHtmlPresenter();

		$useCase = $ffFactory->newMembershipApplicationConfirmationUseCase(
			$presenter,
			$request->query->get( 'accessToken', '' )
		);

		$useCase->showConfirmation( new ShowAppConfirmationRequest( (int)$request->query->get( 'id', 0 ) ) );
		return new Response( $presenter->getHtml() );
	}
}
