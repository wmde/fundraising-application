<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\App\Controllers\Donation;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use WMDE\Fundraising\DonationContext\UseCases\GetDonation\GetDonationRequest;
use WMDE\Fundraising\Frontend\App\AccessDeniedException;
use WMDE\Fundraising\Frontend\App\Routes;
use WMDE\Fundraising\Frontend\Factories\FunFunFactory;

/**
 * @license GPL-2.0-or-later
 * @author Kai Nissen < kai.nissen@wikimedia.de >
 */
class ShowDonationConfirmationController {

	public const SUBMISSION_COOKIE_NAME = 'donation_timestamp';
	public const TIMESTAMP_FORMAT = 'Y-m-d H:i:s';

	public function show( Request $request, FunFunFactory $ffFactory ): Response {
		$useCase = $ffFactory->newGetDonationUseCase( $request->get( 'accessToken', '' ) );

		$responseModel = $useCase->showConfirmation( new GetDonationRequest(
			(int)$request->get( 'id', '' )
		) );

		if ( !$responseModel->accessIsPermitted() ) {
			throw new AccessDeniedException( 'access_denied_donation_confirmation' );
		}

		$ffFactory->getTranslationCollector()->addTranslationFile( $ffFactory->getI18nDirectory() . '/messages/paymentTypes.json' );
		$httpResponse = new Response(
			$ffFactory->newDonationConfirmationPresenter()->present(
				$responseModel->getDonation(),
				$responseModel->getUpdateToken(),
				$request->get( 'accessToken', '' ),
				array_merge(
					Routes::getNamedRouteUrls( $ffFactory->getUrlGenerator() ),
					[
						'updateDonor' => $ffFactory->getUrlGenerator()->generateAbsoluteUrl(
							Routes::UPDATE_DONOR,
							[
								'accessToken' => $request->get( 'accessToken', '' )
							]
						)
					]
				)
			)
		);

		if ( !$request->cookies->get( self::SUBMISSION_COOKIE_NAME ) ) {
			$cookie = $ffFactory->getCookieBuilder();
			$httpResponse->headers->setCookie(
				$cookie->newCookie( self::SUBMISSION_COOKIE_NAME, date( self::TIMESTAMP_FORMAT ) )
			);
		}
		return $httpResponse;
	}

}
