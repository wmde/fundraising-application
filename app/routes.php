<?php

/**
 * These variables need to be in scope when this file is included:
 *
 * @var \Silex\Application $app
 * @var \WMDE\Fundraising\Frontend\FunFunFactory $ffFactory
 */

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use WMDE\Fundraising\Frontend\Domain\Iban;
use WMDE\Fundraising\Frontend\UseCases\DisplayPage\PageDisplayRequest;
use WMDE\Fundraising\Frontend\UseCases\GenerateIban\GenerateIbanRequest;
use WMDE\Fundraising\Frontend\UseCases\ListComments\CommentListingRequest;
use WMDE\Fundraising\Frontend\UseCases\AddSubscription\SubscriptionRequest;

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

$app->post(
	'contact/subscribe',
	function( Request $request ) use ( $ffFactory ) {
		$useCase = $ffFactory->newAddSubscriptionUseCase();
		$subscriptionRequest = SubscriptionRequest::createFromArray( $request->request->all() );
		$subscriptionRequest->setWikiloginFromValues( [
			$request->request->get( 'wikilogin' ),
			$request->cookies->get( 'spenden_wikilogin' ),
		] );
		$responseModel = $useCase->addSubscription( $subscriptionRequest );
		// TODO forward/dispatch to matching 'page/name' route, depending on $responseModel->getType();
		return 'TODO';
	}
);

$app->get(
	'check-iban',
	function( Request $request ) use ( $app, $ffFactory ) {
		$useCase = $ffFactory->newCheckIbanUseCase();
		$responseModel = $useCase->checkIban( new Iban( $request->get( 'iban', '' ) ) );

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
		$useCase = $ffFactory->newGenerateIbanUseCase();
		$responseModel = $useCase->generateIban(
			new GenerateIbanRequest(
				$request->get( 'accountNumber', '' ),
				$request->get( 'bankCode', '' )
			)
		);

		return $app->json( $responseModel ? [ 'status' => 'OK' ] + $responseModel->getBankData() : [ 'status' => 'ERR' ] );
	}
);

return $app;
