<?php

/**
 * These variables need to be in scope when this file is included:
 *
 * @var \Silex\Application $app
 * @var \WMDE\Fundraising\Frontend\FunFunFactory $ffFactory
 */

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use WMDE\Fundraising\Frontend\UseCases\DisplayPage\PageDisplayRequest;
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

$app->get(
	'page/{pageName}',
	function( Application $app, $pageName ) use ( $ffFactory ) {
		return $ffFactory->newDisplayPageUseCase()->getPage( new PageDisplayRequest( $pageName ) );
	}
);

$app->get(
	'check-iban',
	function( Request $request ) use ( $app, $ffFactory ) {
		$useCase = $ffFactory->newValidateBankDataUseCase();
		$responseModel = $useCase->checkIban( $request->get( 'iban', '' ) );

		return $app->json(
			$responseModel ?
				[ 'status' => 'OK' ] + $responseModel->getBankData() :
				[ 'status' => 'ERR' ]
		);
	}
);

$app->get(
	'generate-iban',
	function( Request $request ) use ( $app, $ffFactory ) {
		$useCase = $ffFactory->newValidateBankDataUseCase();
		$responseModel = $useCase->generateIban(
			$request->get( 'accountNumber', '' ),
			$request->get( 'bankCode', '' )
		);

		return $app->json( $responseModel ? [ 'status' => 'OK' ] + $responseModel->getBankData() : [ 'status' => 'ERR' ] );
	}
);

return $app;