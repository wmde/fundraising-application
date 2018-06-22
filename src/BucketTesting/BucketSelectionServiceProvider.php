<?php

declare( strict_types = 1 );


namespace WMDE\Fundraising\Frontend\BucketTesting;

use Pimple\Container;
use Pimple\ServiceProviderInterface;
use Silex\Api\BootableProviderInterface;
use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use WMDE\Fundraising\Frontend\Factories\FunFunFactory;

class BucketSelectionServiceProvider  implements ServiceProviderInterface, BootableProviderInterface {

	private const COOKIE_NAME = 'spenden_ttg';

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
			$selector = $this->factory->getBucketSelector();
			$this->factory->setSelectedBuckets( $selector->selectBuckets( $cookieValues, $request->query->all() ) );
		}, Application::EARLY_EVENT );

		$app->after( function ( Request $request, Response $response ) use ( $app )  {
			$response->headers->setCookie(
				$this->factory->getCookieBuilder()->newCookie(
					self::COOKIE_NAME,
					http_build_query(
						array_merge( ...array_map(
							function( Bucket $bucket ) { return $bucket->getParameters();
				   },
							$this->factory->getSelectedBuckets()
						) )
					)
				)
			);
		} );
	}

}