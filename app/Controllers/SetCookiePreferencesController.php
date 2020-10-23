<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\App\Controllers;

use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use WMDE\Fundraising\Frontend\Infrastructure\TrackingDataSelector;

class SetCookiePreferencesController {

	public const CONSENT_COOKIE_NAME = 'cookie_consent';

	public function index( Request $request ): Response {
		$cookieConsent = $request->get( self::CONSENT_COOKIE_NAME, 'no' );

		if ( $cookieConsent === 'yes' ) {
			$request->attributes->set( 'trackingCode', TrackingDataSelector::getFirstNonEmptyValue( [
				$request->cookies->get( 'spenden_tracking' ),
				TrackingDataSelector::concatTrackingFromVarTuple(
					$request->get( 'piwik_campaign', '' ),
					$request->get( 'piwik_kwd', '' )
				)
			] ) );
		}

		$response = JsonResponse::create( [
			'status' => 'OK',
		] );

		$response->headers->setCookie( new Cookie( self::CONSENT_COOKIE_NAME, $cookieConsent ) );

		return $response;
	}
}
