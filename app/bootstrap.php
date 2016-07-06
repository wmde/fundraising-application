<?php

/**
 * These variables need to be in scope when this file is included:
 *
 * @var \WMDE\Fundraising\Frontend\Factories\FunFunFactory $ffFactory
 */

declare( strict_types = 1 );

use Silex\Application;
use Silex\Provider\SessionServiceProvider;
use Silex\Provider\UrlGeneratorServiceProvider;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use WMDE\Fundraising\Frontend\App\AccessDeniedException;
use WMDE\Fundraising\Frontend\Infrastructure\BrowserCookieHandler;

$app = new Application();

$app->register( new SessionServiceProvider() );
$app->register( new UrlGeneratorServiceProvider() );

$app->after( function( Request $request, Response $response ) {
	if( $response instanceof JsonResponse ) {
		$response->setEncodingOptions( JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES );
	}

	return $response;
} );

$app->before(
	function ( Request $request, Application $app ) {
		$app['request.is_json'] = in_array( 'application/json', $request->getAcceptableContentTypes() );
	},
	Application::EARLY_EVENT
);

$app->error( function ( AccessDeniedException $e, $code ) use ( $ffFactory ) {
	return new Response(
		$ffFactory->newAccessDeniedHTMLPresenter()->present( $e ),
		403,
		[ 'X-Status-Code' => 403 ]
	);
} );

$app->error( function ( \Exception $e, $code ) use ( $ffFactory, $app ) {
	if ( $app['debug'] ) {
		throw $e;
	}

	$ffFactory->getLogger()->error( $e->getMessage(), [
		'code' => $e->getCode(),
		'file' => $e->getFile(),
		'line' => $e->getLine(),
		'stack_trace' => $e->getTraceAsString()
	] );

	if ( $app['request.is_json'] ) {
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