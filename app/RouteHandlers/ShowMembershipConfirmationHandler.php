<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\App\RouteHandlers;

use WMDE\Fundraising\Frontend\Factories\FunFunFactory;

/**
 * @license GNU GPL v2+
 * @author Kai Nissen < kai.nissen@wikimedia.de >
 */
class ShowMembershipConfirmationHandler {

	public const SUBMISSION_COOKIE_NAME = 'memapp_timestamp';
	public const TIMESTAMP_FORMAT = 'Y-m-d H:i:s';

	private $ffFactory;

	public function __construct( FunFunFactory $ffFactory ) {
		$this->ffFactory = $ffFactory;
	}

	// FIXME: this code is not called anywhere?!
//	public function handle( Request $request ): Response {
//		$useCase = $this->ffFactory->newMembershipApplicationConfirmationUseCase( $request->get( 'accessToken', '' ) );
//
//		$responseModel = $useCase->showConfirmation( new ShowAppConfirmationRequest(
//			(int)$request->get( 'id', '' )
//		) );
//
//		if ( $responseModel->accessIsPermitted() ) {
//			$httpResponse = new Response(
//				$this->ffFactory->newMembershipApplicationConfirmationHtmlPresenter()->present( $responseModel )
//			);
//
//			if ( $request->cookies->get( self::SUBMISSION_COOKIE_NAME ) ) {
//				$cookie = $this->ffFactory->getCookieBuilder();
//				$httpResponse->headers->setCookie(
//					$cookie->newCookie( self::SUBMISSION_COOKIE_NAME, date( self::TIMESTAMP_FORMAT ) )
//				);
//			}
//
//			return $httpResponse;
//		}
//
//		throw new AccessDeniedException( 'access_denied_donation_confirmation' );
//	}

}