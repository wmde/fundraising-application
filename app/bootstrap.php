<?php

/**
 * These variables need to be in scope when this file is included:
 *
 * @var \WMDE\Fundraising\Frontend\Factories\FunFunFactory $ffFactory
 */

declare( strict_types = 1 );

use Silex\Application;
use Silex\Provider\RoutingServiceProvider;
use Silex\Provider\SessionServiceProvider;
use Silex\Provider\TwigServiceProvider;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use WMDE\Fundraising\Frontend\App\AccessDeniedException;
use WMDE\Fundraising\Frontend\BucketTesting\BucketSelectionServiceProvider;
use WMDE\Fundraising\Frontend\Infrastructure\TrackingDataSelector;
use WMDE\Fundraising\Frontend\Presentation\SkinServiceProvider;
use WMDE\Fundraising\Frontend\Presentation\SofortToggleServiceProvider;

$app = new Application();

$app->register( new SessionServiceProvider() );
$app->register( new RoutingServiceProvider() );
$app->register( new TwigServiceProvider() );
$app->register( new BucketSelectionServiceProvider( $ffFactory ) );
$app->register( new SofortToggleServiceProvider( $ffFactory->getPaymentTypesSettings() ) );

$app->before(
	function ( Request $request, Application $app ) {
		$app['request_stack.is_json'] = in_array( 'application/json', $request->getAcceptableContentTypes() );

		$request->attributes->set( 'trackingCode', TrackingDataSelector::getFirstNonEmptyValue( [
			$request->cookies->get( 'spenden_tracking' ),
			$request->request->get( 'tracking' ),
			TrackingDataSelector::concatTrackingFromVarTuple(
				$request->get( 'piwik_campaign', '' ),
				$request->get( 'piwik_kwd', '' )
			)
		] ) );

		$request->attributes->set( 'trackingSource', TrackingDataSelector::getFirstNonEmptyValue( [
			$request->cookies->get( 'spenden_source' ),
			$request->request->get( 'source' ),
			$request->server->get( 'HTTP_REFERER' )
		] ) );
	},
	Application::EARLY_EVENT
);

$app->before( function( Request $request ) {
	foreach ( [ $request->request, $request->query ] as $parameterBag ) {
		foreach ( $parameterBag->keys() as $key ) {
			if ( is_string( $parameterBag->get( $key ) ) ) {
				$parameterBag->set( $key, trim( $parameterBag->get( $key ) ) );
			}
		}
	}
}, Application::EARLY_EVENT );

$app->after( function( Request $request, Response $response, Application $app ) {
	if( $response instanceof JsonResponse ) {
		$response->setEncodingOptions( JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES );
	}

	// Set cookie with original tracking data
	if ( (string)$request->cookies->get( 'spenden_tracking' ) === '' &&
			(string)$request->get( 'piwik_campaign' ) !== '' && (string)$request->get( 'piwik_kwd' ) !== '' ) {
		$response->headers->setCookie( new \Symfony\Component\HttpFoundation\Cookie(
			'spenden_tracking',
			$request->get( 'piwik_campaign' ) . '/' . $request->get( 'piwik_kwd' )
		) );
	}

	return $response;
} );

$app->error( function ( AccessDeniedException $e ) use ( $ffFactory ) {
	return new Response(
		$ffFactory->newAccessDeniedHtmlPresenter()->present( $e ),
		403,
		[ 'X-Status-Code' => 403 ]
	);
} );

$app->error( function ( NotFoundHttpException $e ) use ( $ffFactory, $app ) {
	if ( $app['request_stack.is_json'] ) {
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

	$ffFactory->getLogger()->error( $e->getMessage(), [
		'code' => $e->getCode(),
		'file' => $e->getFile(),
		'line' => $e->getLine(),
		'stack_trace' => $e->getTraceAsString()
	] );

	if ( $app['request_stack.is_json'] ) {
		return $app->json( [
			'ERR' => $e->getMessage()
		] );
	}

	return new Response(
		$ffFactory->newInternalErrorHtmlPresenter()->present( $e ),
		$code
	);
} );

return require __DIR__ . '/routes.php';