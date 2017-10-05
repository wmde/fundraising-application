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

	public function boot( Application $app ): void {
		$app->before( function( Request $request ): void {
			$skinFromCookie = $this->getSkinFromCookie( $request );
			if ( $skinFromCookie ) {
				$this->skinSettings->setSkin( $skinFromCookie );
			}

			$skinFromQuery = $this->getSkinFromQuery( $request );
			if ( $skinFromQuery && $skinFromQuery !== $this->skinSettings->getSkin() ) {
				$this->skinSettings->setSkin( $skinFromQuery );
				$this->updatedSkin = $skinFromQuery;
			}
		}, Application::EARLY_EVENT );

		$app->after( function( Request $request, Response $response ): void {
			if ( !$this->updatedSkin ) {
				return;
			}

			if ( $this->updatedSkin === $this->skinSettings->getDefaultSkin() ) {
				$cookie = $this->cookieBuilder->newCookie(
					SkinSettings::COOKIE_NAME,
					'',
					time() - 3600
				);
			} else {
				$cookie = $this->cookieBuilder->newCookie(
					SkinSettings::COOKIE_NAME,
					$this->updatedSkin,
					time() + $this->skinSettings->getCookieLifetime()
				);
			}

			$response->headers->setCookie( $cookie );
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
