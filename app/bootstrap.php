<?php

/**
 * These variables need to be in scope when this file is included:
 *
 * @var \WMDE\Fundraising\Frontend\FFFactory $ffFactory
 */

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

$app = new \Silex\Application();

$app->after( function( Request $request, Response $response ) {
	if( $response instanceof JsonResponse ) {
		$response->setEncodingOptions( JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES );
	}

	return $response;
} );

$app->error( function ( \Exception $e, $code ) {
	return new JsonResponse(
		[
			'message' => $e->getMessage(),
			'code' => $code
		],
		$code
	);
} );

return require __DIR__ . '/routes.php';