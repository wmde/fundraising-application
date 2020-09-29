<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\App;

use Silex\Application;
use Silex\Provider\RoutingServiceProvider;
use Silex\Provider\SessionServiceProvider;
use Silex\Provider\TwigServiceProvider;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use WMDE\Fundraising\Frontend\App\EventHandlers\AddIndicatorAttributeForJsonRequests;
use WMDE\Fundraising\Frontend\App\EventHandlers\LogErrors;
use WMDE\Fundraising\Frontend\App\EventHandlers\PrettifyJsonResponse;
use WMDE\Fundraising\Frontend\App\EventHandlers\RegisterTrackingData;
use WMDE\Fundraising\Frontend\App\EventHandlers\TrimEveryInput;
use WMDE\Fundraising\Frontend\BucketTesting\BucketSelectionServiceProvider;
use WMDE\Fundraising\Frontend\Factories\FunFunFactory;

class Bootstrap {

	public static function initializeApplication( FunFunFactory $ffFactory ): Application {
		$app = new Application();

		$app->register( new SessionServiceProvider() );
		$app->register( new RoutingServiceProvider() );
		$app->register( new TwigServiceProvider() );
		$app->register( new BucketSelectionServiceProvider( $ffFactory ) );
		$app->register( new FundraisingFactoryServiceProvider( $ffFactory ) );

		$app->extend( 'dispatcher', function ( EventDispatcher $dispatcher ) use ( $ffFactory ) {
			$dispatcher->addSubscriber( new AddIndicatorAttributeForJsonRequests() );
			$dispatcher->addSubscriber( new RegisterTrackingData() );
			$dispatcher->addSubscriber( new TrimEveryInput() );
			$dispatcher->addSubscriber( new LogErrors( $ffFactory->getLogger() ) );

			$environment = $_ENV['APP_ENV'] ?? 'dev';
			if ( $environment === 'test' || $environment === 'dev' ) {
				$dispatcher->addSubscriber( new PrettifyJsonResponse() );
			}
			return $dispatcher;
		} );

		$app->error( function ( AccessDeniedException $e ) use ( $ffFactory ) {
			return new Response(
				$ffFactory->newAccessDeniedHtmlPresenter()->present( $e->getMessage() ),
				403,
				[ 'X-Status-Code' => 403 ]
			);
		} );

		$app->error( function ( NotFoundHttpException $e, Request $request ) use ( $ffFactory, $app ) {
			if ( $request->attributes->get( 'request_stack.is_json', false ) ) {
				return $app->json( [ 'ERR' => $e->getMessage() ], 404, [ 'X-Status-Code' => 404 ] );
			}

			return new Response(
				$ffFactory->newPageNotFoundHtmlPresenter()->present(),
				404,
				[ 'X-Status-Code' => 404 ]
			);
		} );

		$app->error( function ( \Exception $e, Request $request, $code ) use ( $ffFactory, $app ) {
			if ( $app['debug'] ) {
				throw $e;
			}

			if ( $request->attributes->get( 'request_stack.is_json', false ) ) {
				return $app->json( [
					'ERR' => $e->getMessage()
				] );
			}

			return new Response(
				$ffFactory->getInternalErrorHtmlPresenter()->present( $e ),
				$code
			);
		} );

		return Routes::initializeRoutes( $app, $ffFactory );
	}

}
