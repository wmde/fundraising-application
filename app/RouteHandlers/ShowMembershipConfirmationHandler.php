<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\App\RouteHandlers;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use WMDE\Fundraising\Frontend\App\AccessDeniedException;
use WMDE\Fundraising\Frontend\Factories\FunFunFactory;
use WMDE\Fundraising\Frontend\MembershipContext\UseCases\ShowApplicationConfirmation\ShowAppConfirmationRequest;

/**
 * @license GNU GPL v2+
 * @author Kai Nissen < kai.nissen@wikimedia.de >
 */
class ShowMembershipConfirmationHandler {

	const SUBMISSION_COOKIE_NAME = 'memapp_timestamp';
	const TIMESTAMP_FORMAT = 'Y-m-d H:i:s';

	private $ffFactory;

	public function __construct( FunFunFactory $ffFactory ) {
		$this->ffFactory = $ffFactory;
	}

	public function handle( Request $request ): Response {
		$useCase = $this->ffFactory->newMembershipApplicationConfirmationUseCase( $request->get( 'accessToken', '' ) );

		$responseModel = $useCase->showConfirmation( new ShowAppConfirmationRequest(
			(int)$request->get( 'id', '' )
		) );

		if ( $responseModel->accessIsPermitted() ) {
			$httpResponse = new Response(
				$this->ffFactory->newMembershipApplicationConfirmationHtmlPresenter()->present( $responseModel )
			);

			if ( $request->cookies->get( self::SUBMISSION_COOKIE_NAME ) ) {
				$cookie = $this->ffFactory->getCookieBuilder();
				$httpResponse->headers->setCookie(
					$cookie->newCookie( self::SUBMISSION_COOKIE_NAME, date( self::TIMESTAMP_FORMAT ) )
				);
			}

			return $httpResponse;
		}

		throw new AccessDeniedException( 'access_denied_donation_confirmation' );
	}

}