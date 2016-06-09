<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\App\RouteHandlers;

use Silex\Application;
use Symfony\Component\HttpFoundation\Response;

/**
 * @license GNU GPL v2+
 * @author Kai Nissen < kai.nissen@wikimedia.de >
 */
class RouteRedirectionHandler {

	private $app;
	private $queryString;

	public function __construct( Application $app, string $queryString = null ) {
		$this->app = $app;
		$this->queryString = $queryString;
	}

	public function handle( string $redirectionTarget ): Response {
		return $this->app->redirect( $redirectionTarget . $this->getQueryString() );
	}

	private function getQueryString(): string {
		if ( empty ( $this->queryString ) ) {
			return '';
		}

		return '?' . $this->queryString;
	}

}