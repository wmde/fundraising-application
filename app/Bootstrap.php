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
use WMDE\Fundraising\Frontend\App\EventHandlers\RegisterTrackingData;
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

		$app->extend( 'dispatcher', function ( EventDispatcher $dispatcher ) {
			$dispatcher->addSubscriber( new RegisterTrackingData() );
			return $dispatcher;
		} );

		$app->before(
			function ( Request $request ) {
				$request->attributes->set( 'request_stack.is_json', in_array( 'application/json', $request->getAcceptableContentTypes() ) );
				if ( in_array( 'application/javascript', $request->getAcceptableContentTypes() ) && $request->get( 'callback', null ) ) {
					$request->attributes->set( 'request_stack.is_json', true );
				}
			},
			Application::EARLY_EVENT
		);

		$app->before( function ( Request $request ) {
			foreach ( [ $request->request, $request->query ] as $parameterBag ) {
				foreach ( $parameterBag->keys() as $key ) {
					if ( is_string( $parameterBag->get( $key ) ) ) {
						$parameterBag->set( $key, trim( $parameterBag->get( $key ) ) );
					}
				}
			}
		}, Application::EARLY_EVENT );

		$app->after( function ( Request $request, Response $response ) {
			if ( $response instanceof JsonResponse ) {
				$response->setEncodingOptions( JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES );
			}

			return $response;
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

			$ffFactory->getLogger()->error(
				$e->getMessage(),
				[
					'code' => $e->getCode(),
					'file' => $e->getFile(),
					'line' => $e->getLine(),
					'stack_trace' => $e->getTraceAsString(),
					'referrer' => $request->headers->get( 'referer' ),
					'uri' => $request->getRequestUri(),
					'languages' => $request->getLanguages(),
					'charsets' => $request->getCharsets(),
					'content_types' => $request->getAcceptableContentTypes(),
					'method' => $request->getMethod()
				]
			);

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
