<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\App\Controllers;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use WMDE\Fundraising\Frontend\App\CookieNames;
use WMDE\Fundraising\Frontend\Factories\FunFunFactory;
use WMDE\Fundraising\Frontend\Infrastructure\TrackingDataSelector;

class SetCookiePreferencesController {

	public function index( FunFunFactory $ffFactory, Request $request ): Response {
		$cookieConsent = $request->get( CookieNames::CONSENT, 'no' );

		if ( $cookieConsent === 'yes' ) {
			$request->attributes->set( 'trackingCode', TrackingDataSelector::getFirstNonEmptyValue( [
				$request->cookies->get( CookieNames::TRACKING ),
				TrackingDataSelector::concatTrackingFromVarTuple(
					$request->get( 'piwik_campaign', '' ),
					$request->get( 'piwik_kwd', '' )
				)
			] ) );
		}

		$response = new JsonResponse( [
			'status' => 'OK',
		] );

		$response->headers->setCookie( $ffFactory->getCookieBuilder()->newCookie(
			CookieNames::CONSENT,
			$cookieConsent
		) );

		if ( $cookieConsent === 'no' ) {
			$response->headers->clearCookie( CookieNames::TRACKING );
			$response->headers->clearCookie( CookieNames::BUCKET_TESTING );
		}

		return $response;
	}
}
