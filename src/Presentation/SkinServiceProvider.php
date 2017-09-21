<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Presentation;

use Pimple\Container;
use Pimple\ServiceProviderInterface;
use Silex\Api\BootableProviderInterface;
use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use WMDE\Fundraising\Frontend\Infrastructure\CookieBuilder;

class SkinServiceProvider implements ServiceProviderInterface, BootableProviderInterface {

	private $skinSettings;
	private $cookieBuilder;

	private $updatedSkin;

	public function __construct( SkinSettings $skinSettings, CookieBuilder $cookieBuilder ) {
		$this->skinSettings = $skinSettings;
		$this->cookieBuilder = $cookieBuilder;
	}

	/**
	 * Ideally, SkinSettings would be registered to app as a service here - but $pimple['twig'] in FunFunFactory needs it
	 * and does not have access to app
	 */
	public function register( Container $app ): void {
	}

	public function boot( Application $app ) {
		$app->before( function( Request $request ) {
			$skin = $this->getSkinFromQuery( $request );
			if ( $skin && $skin !== $this->skinSettings->getDefaultSkin() ) {
				$this->updatedSkin = $skin;
				$this->skinSettings->setSkin( $skin );
				return;
			}

			$skin = $this->getSkinFromCookie( $request );
			if ( $skin ) {
				$this->skinSettings->setSkin( $skin );
			}
		}, Application::EARLY_EVENT );

		$app->after( function( Request $request, Response $response ) {
			if ( !$this->updatedSkin ) {
				return;
			}

			$response->headers->setCookie(
				$this->cookieBuilder->newCookie(
					SkinSettings::COOKIE_NAME,
					$this->updatedSkin,
					time() + $this->skinSettings->getCookieLifetime()
				)
			);
		} );
	}

	private function getSkinFromQuery( Request $request ): ?string {
		$skin = $request->query->get( SkinSettings::QUERY_PARAM_NAME, '' );
		return $this->skinSettings->isValidSkin( $skin ) ? $skin : null;
	}

	private function getSkinFromCookie( Request $request ): ?string {
		$skin = $request->cookies->get( SkinSettings::COOKIE_NAME, '' );
		return $this->skinSettings->isValidSkin( $skin ) ? $skin : null;
	}
}
