<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Presentation;

use Pimple\Container;
use Pimple\ServiceProviderInterface;
use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use WMDE\Fundraising\Frontend\Infrastructure\CookieBuilder;

class SkinServiceProvider implements ServiceProviderInterface {

	private $skinManager;
	private $cookieBuilder;

	private $updatedSkin;

	public function __construct( SkinManager $skinManager, CookieBuilder $cookieBuilder ) {
		$this->skinManager = $skinManager;
		$this->cookieBuilder = $cookieBuilder;
	}

	public function register( Container $app ): void {

		$app->before( function( Request $request, Application $app ) {

			$skin = $this->getSkinFromQuery( $request );
			if ( $skin && $skin !== $this->skinManager->getDefaultSkin() ) {
				$this->updatedSkin = $skin;
				$this->skinManager->setSkin( $skin );
				return;
			}

			$skin = $this->getSkinFromCookie( $request );
			if ( $skin ) {
				$this->skinManager->setSkin( $skin );
			}

		}, Application::EARLY_EVENT );

		$app->after( function( Request $request, Response $response, Application $app ) {

			if ( !$this->updatedSkin ) {
				return;
			}

			$response->headers->setCookie(
				$this->cookieBuilder->newCookie(
					SkinManager::COOKIE_NAME,
					$this->updatedSkin,
					time() + $this->skinManager->getCookieLifetime()
				)
			);
		} );
	}

	private function getSkinFromQuery( Request $request ): ?string {
		$skin = $request->query->get( SkinManager::QUERY_PARAM_NAME, '' );
		return $this->skinManager->isValidSkin( $skin ) ? $skin : null;
	}

	private function getSkinFromCookie( Request $request ): ?string {
		$skin = $request->cookies->get( SkinManager::COOKIE_NAME, '' );
		return $this->skinManager->isValidSkin( $skin ) ? $skin : null;
	}
}