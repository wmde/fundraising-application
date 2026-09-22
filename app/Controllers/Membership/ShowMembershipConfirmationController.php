<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\App\Controllers\Membership;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use WMDE\Fundraising\Frontend\App\Routes;
use WMDE\Fundraising\Frontend\Authentication\AuthenticationBoundedContext;
use WMDE\Fundraising\Frontend\Authentication\OldStyleTokens\NullToken;
use WMDE\Fundraising\Frontend\Factories\FunFunFactory;
use WMDE\Fundraising\MembershipContext\UseCases\ShowApplicationConfirmation\ShowAppConfirmationRequest;

class ShowMembershipConfirmationController {

	public function index( FunFunFactory $ffFactory, Request $request ): Response {
		$ffFactory->getTranslationCollector()->addTranslationFile( $ffFactory->getI18nDirectory() . '/messages/paymentTypes.json' );

		$membershipId = (int)$request->query->get( 'id', 0 );

		$accessToken = $request->query->get( 'accessToken', '' );
		$updateToken = $this->getUpdateToken( $ffFactory, $membershipId );
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
		$presenter = $ffFactory->newMembershipApplicationConfirmationHtmlPresenter( $urls, $updateToken );
		$useCase = $ffFactory->newMembershipApplicationConfirmationUseCase( $presenter, $accessToken, $updateToken );

		$useCase->showConfirmation( new ShowAppConfirmationRequest( $membershipId ) );
		return new Response( $presenter->getHtml() );
	}

	private function getUpdateToken( FunFunFactory $ffFactory, int $membershipId ): string {
		$token = $ffFactory->getTokenRepository()->getTokenById( $membershipId, AuthenticationBoundedContext::Membership );

		if ( $token instanceof NullToken ) {
			return '';
		}

		return $token->getUpdateToken();
	}
}
