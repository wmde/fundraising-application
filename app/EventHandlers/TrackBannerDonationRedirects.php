<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\App\EventHandlers;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * When a user makes an anonymous donation from a banner the data gets posted directly to
 * add donation which forwards them to the external payment processor. They're then
 * returned to the confirmation page on a successful payment. That causes the
 * campaign tracking information to be dropped from the URL and messes up our statistics.
 * This event handler adds temporary session values that are restored to the url and
 * deleted from the session upon a successful payment.
 */
class TrackBannerDonationRedirects implements EventSubscriberInterface {

	public const PIWIK_CAMPAIGN = 'piwik_campaign';
	public const PIWIK_KWD = 'piwik_kwd';

	private SessionInterface $session;
	private string $submissionRoute;
	private string $confirmationRoute;
	private string $bannerSubmissionUrlParameter;

	public static function getSubscribedEvents() {
		return [
			KernelEvents::REQUEST => 'onKernelRequest',
			KernelEvents::RESPONSE => 'onKernelResponse'
		];
	}

	public function __construct( SessionInterface $session, string $submissionRoute, string $confirmationRoute, string $bannerSubmissionUrlParameter ) {
		$this->session = $session;
		$this->submissionRoute = $submissionRoute;
		$this->confirmationRoute = $confirmationRoute;
		$this->bannerSubmissionUrlParameter = $bannerSubmissionUrlParameter;
	}

	public function onKernelRequest( RequestEvent $event ): void {
		$request = $event->getRequest();

		switch ( $request->attributes->get( '_route' ) ) {
			case $this->submissionRoute:
				$this->storeCampaignParameters( $request );
				break;
			case $this->confirmationRoute:
				$this->restoreCampaignParameters( $event, $request );
				break;
		}
	}

	public function onKernelResponse( ResponseEvent $event ): void {
		$request = $event->getRequest();

		if ( $request->attributes->get( '_route' ) !== $this->submissionRoute ) {
			$this->session->remove( self::PIWIK_CAMPAIGN );
			$this->session->remove( self::PIWIK_KWD );
		}
	}

	private function storeCampaignParameters( Request $request ): void {
		if ( !$this->requestWasSubmittedFromBanner( $request ) ) {
			return;
		}

		$this->session->set( self::PIWIK_CAMPAIGN, $request->get( self::PIWIK_CAMPAIGN, '' ) );
		$this->session->set( self::PIWIK_KWD, $request->get( self::PIWIK_KWD, '' ) );
	}

	private function restoreCampaignParameters( RequestEvent $event, Request $request ): void {
		if ( $this->hasQueryTracking( $request ) || !$this->hasSessionCampaignParameters() ) {
			return;
		}

		$campaignKey = self::PIWIK_CAMPAIGN;
		$keywordKey = self::PIWIK_KWD;
		$campaign = $this->session->get( $campaignKey );
		$keyword = $this->session->get( $keywordKey );

		$separator = $request->getQueryString() ? '&' : '?';
		$extraQueryParameters = "{$campaignKey}={$campaign}&{$keywordKey}={$keyword}";
		$event->setResponse( new RedirectResponse(
			$request->getRequestUri() . $separator . $extraQueryParameters
		) );
	}

	private function requestWasSubmittedFromBanner( Request $request ): bool {
		return $request->get( $this->bannerSubmissionUrlParameter ) !== null;
	}

	private function hasQueryTracking( Request $request ): bool {
		return $request->get( self::PIWIK_CAMPAIGN ) || $request->get( self::PIWIK_KWD );
	}

	private function hasSessionCampaignParameters(): bool {
		return $this->session->has( self::PIWIK_CAMPAIGN ) && $this->session->has( self::PIWIK_KWD );
	}

}
