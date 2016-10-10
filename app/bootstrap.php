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
use WMDE\Fundraising\Frontend\App\AccessDeniedException;

$app = new Application();

$app->register( new SessionServiceProvider() );
$app->register( new RoutingServiceProvider() );
$app->register( new TwigServiceProvider() );

$app->before(
	function ( Request $request, Application $app ) {
		$app['request_stack.is_json'] = in_array( 'application/json', $request->getAcceptableContentTypes() );
	},
	Application::EARLY_EVENT
);

$app->after( function( Request $request, Response $response ) {
	if( $response instanceof JsonResponse ) {
		$response->setEncodingOptions( JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES );
	}

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
		$ffFactory->newAccessDeniedHTMLPresenter()->present( $e ),
		403,
		[ 'X-Status-Code' => 403 ]
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
		$ffFactory->newInternalErrorHTMLPresenter()->present( $e ),
		$code
	);
} );

return require __DIR__ . '/routes.php';