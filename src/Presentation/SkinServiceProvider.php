<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Presentation;

use Pimple\Container;
use Pimple\ServiceProviderInterface;
use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use WMDE\Fundraising\Frontend\Factories\FunFunFactory;

class SkinServiceProvider implements ServiceProviderInterface {

	private const GET_PARAM_NAME = 'skin';
	private const COOKIE_NAME = 'skin';

	private $ffFactory;
	private $updatedSkin;

	public function __construct( FunFunFactory $factory ) {
		$this->ffFactory = $factory;
	}

	public function register( Container $app ): void {

		$app->before( function( Request $request, Application $app ) {

			$skin = $this->getSkinFromQuery( $request );
			if ( $skin ) {
				$this->updatedSkin = $skin;
				$this->ffFactory->setSkin( $skin );
				return;
			}

			$skin = $this->getSkinFromCookie( $request );
			if ( $skin ) {
				$this->ffFactory->setSkin( $skin );
			}

		}, Application::EARLY_EVENT );

		$app->after( function( Request $request, Response $response, Application $app ) {

			if ( !$this->updatedSkin ) {
				return;
			}

			$cookieBuilder = $this->ffFactory->newCookieBuilder();
			$cookie = $cookieBuilder->newCookie( self::COOKIE_NAME, $this->updatedSkin );
			$response->headers->setCookie( $cookie );
		} );
	}

	private function getSkinFromQuery( Request $request ): ?string {
		$skin = $request->query->get( self::GET_PARAM_NAME, '' );
		return $this->validateSkin( $skin ) ? $skin : null;
	}

	private function getSkinFromCookie( Request $request ): ?string {
		$skin = $request->cookies->get( self::COOKIE_NAME, '' );
		return $this->validateSkin( $skin ) ? $skin : null;
	}

	private function validateSkin( string $skin ): bool {
		return in_array( $skin, $this->ffFactory->getSkinOptions(), true );
	}
}