<?php

/**
 * These variables need to be in scope when this file is included:
 *
 * @var \Silex\Application $app
 * @var \WMDE\Fundraising\Frontend\FFFactory $ffFactory
 */

use Symfony\Component\HttpFoundation\Request;
use WMDE\Fundraising\Frontend\UseCases\ListComments\CommentListingRequest;

$app->get(
	'validate-email',
	function( Request $request ) use ( $app, $ffFactory ) {
		$useCase = $ffFactory->newValidateEmailUseCase();
		$responseModel = $useCase->validateEmail( $request->get( 'email', '' ) );

		// Presenter code:
		return $app->json( [ 'status' => $responseModel ? 'OK' : 'ERR' ] );
	}
);

$app->get(
	'list.rss',
	function( Request $request ) use ( $app, $ffFactory ) {
		$useCase = $ffFactory->newListCommentsUseCase();
		$responseModel = $useCase->listComments( new CommentListingRequest( 10 /* TODO: get real limit */ ) );

		// Presenter code:
		return 'TODO';
	}
);

return $app;