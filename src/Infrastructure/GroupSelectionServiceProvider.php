<?php

declare( strict_types = 1 );


namespace WMDE\Fundraising\Frontend\Infrastructure;

use Pimple\Container;
use Pimple\ServiceProviderInterface;
use Silex\Api\BootableProviderInterface;
use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use WMDE\Fundraising\Frontend\Factories\FunFunFactory;

class GroupSelectionServiceProvider  implements ServiceProviderInterface, BootableProviderInterface {

	private const COOKIE_NAME = 'spenden_ttg';
	private const COOKIE_LIFETIME_IN_SECONDS = 60 * 60 * 24 * 90; // 90 days

	private $factory;

	public function __construct( FunFunFactory $factory ) {
		$this->factory = $factory;
	}

	/**
	 *
	 * @param Container $app
	 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
	 */
	public function register( Container $app ) {
		// empty function for satisfying the interface
	}

	public function boot( Application $app ) {
		$app->before( function( Request $request ): void {
			parse_str( $request->cookies->get( 'spenden_ttg', '' ), $cookieValues );
			$selector = $this->factory->getGroupSelector();
			$selector
				->setUrlParameters( $request->query->all() )
				->setCookie( $cookieValues );
			$this->factory->setSelectedGroups( $selector->selectGroups() );
		}, Application::EARLY_EVENT );

		$app->after( function ( Request $request, Response $response) use ($app)  {
			$response->headers->setCookie(
				$this->factory->getCookieBuilder()->newCookie(
					self::COOKIE_NAME,
					http_build_query(
						array_merge( ...array_map(
							function( Group $group ) { return $group->getUrlParameter(); },
							$this->factory->getSelectedGroups()
						) )
					),
					time() + self::COOKIE_LIFETIME_IN_SECONDS
				)
			);
		} );
	}

}