<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\App;

use Silex\Application;
use Silex\Provider\RoutingServiceProvider;
use Silex\Provider\SessionServiceProvider;
use Silex\Provider\TwigServiceProvider;
use Symfony\Component\EventDispatcher\EventDispatcher;
use WMDE\Fundraising\Frontend\App\EventHandlers\AddIndicatorAttributeForJsonRequests;
use WMDE\Fundraising\Frontend\App\EventHandlers\HandleExceptions;
use WMDE\Fundraising\Frontend\App\EventHandlers\LogErrors;
use WMDE\Fundraising\Frontend\App\EventHandlers\PrettifyJsonResponse;
use WMDE\Fundraising\Frontend\App\EventHandlers\RegisterTrackingData;
use WMDE\Fundraising\Frontend\App\EventHandlers\StoreBucketSelection;
use WMDE\Fundraising\Frontend\App\EventHandlers\TrimEveryInput;
use WMDE\Fundraising\Frontend\Factories\FunFunFactory;

class Bootstrap {

	public static function initializeApplication( FunFunFactory $ffFactory ): Application {
		$app = new Application();

		$app->register( new SessionServiceProvider() );
		$app->register( new RoutingServiceProvider() );
		$app->register( new TwigServiceProvider() );
		$app->register( new FundraisingFactoryServiceProvider( $ffFactory ) );

		$app->extend( 'dispatcher', function ( EventDispatcher $dispatcher ) use ( $ffFactory ) {
			$dispatcher->addSubscriber( new StoreBucketSelection( $ffFactory ) );
			$dispatcher->addSubscriber( new AddIndicatorAttributeForJsonRequests() );
			$dispatcher->addSubscriber( new RegisterTrackingData() );
			$dispatcher->addSubscriber( new TrimEveryInput() );
			$dispatcher->addSubscriber( new LogErrors( $ffFactory->getLogger() ) );
			$dispatcher->addSubscriber( new HandleExceptions( $ffFactory ) );

			$environment = $_ENV['APP_ENV'] ?? 'dev';
			if ( $environment === 'test' || $environment === 'dev' ) {
				$dispatcher->addSubscriber( new PrettifyJsonResponse() );
			}
			return $dispatcher;
		} );

		// Disable Silex error handler, we handle errors with HandleExceptions class
		unset( $app['exception_handler'] );

		return Routes::initializeRoutes( $app, $ffFactory );
	}

}
