<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\App\Controllers\Membership;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use WMDE\Fundraising\Frontend\App\Routes;
use WMDE\Fundraising\Frontend\Factories\FunFunFactory;
use WMDE\Fundraising\MembershipContext\UseCases\ShowApplicationConfirmation\ShowAppConfirmationRequest;

class ShowMembershipConfirmationController {

	public function index( FunFunFactory $ffFactory, Request $request ): Response {
		$ffFactory->getTranslationCollector()->addTranslationFile( $ffFactory->getI18nDirectory() . '/messages/paymentTypes.json' );

		$accessToken = $request->query->get( 'accessToken', '' );
		$urls = array_merge(
			Routes::getNamedRouteUrls( $ffFactory->getUrlGenerator() ),
			[
				'updateMembershipApplication' => $ffFactory->getUrlGenerator()->generateAbsoluteUrl(
					Routes::API_UPDATE_MEMBERSHIP_APPLICATION_PUT,
					[
						'accessToken' => $accessToken
					]
				)
			]
		);
		$presenter = $ffFactory->newMembershipApplicationConfirmationHtmlPresenter( $urls );
		$useCase = $ffFactory->newMembershipApplicationConfirmationUseCase( $presenter, $accessToken );

		$useCase->showConfirmation( new ShowAppConfirmationRequest( (int)$request->query->get( 'id', 0 ) ) );
		return new Response( $presenter->getHtml() );
	}
}
