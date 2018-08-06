<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\App\RouteHandlers;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use WMDE\Fundraising\Frontend\Factories\FunFunFactory;
use WMDE\Fundraising\SubscriptionContext\UseCases\AddSubscription\SubscriptionRequest;
use WMDE\FunValidators\ValidationResponse;

/**
 * @license GNU GPL v2+
 * @author Gabriel Birke < gabriel.birke@wikimedia.de >
 */
class AddSubscriptionHandler {

	private $ffFactory;
	private $app;

	public function __construct( FunFunFactory $ffFactory, Application $app ) {
		$this->ffFactory = $ffFactory;
		$this->app = $app;
	}

	public function handle( Request $request ): Response {

		$useCase = $this->ffFactory->newAddSubscriptionUseCase();
		$responseModel = $useCase->addSubscription( $this->createSubscriptionRequest( $request ) );

		if ( $request->query->has( 'callback' ) ) {
			return $this->handleJSONP( $request, $responseModel );
		} elseif ( $this->app['request_stack.is_json'] ) {
			return $this->handleJSON( $responseModel );
		} else {
			return $this->handleHTML( $request, $responseModel );
		}
	}

	private function createSubscriptionRequest( Request $request ): SubscriptionRequest {
		$subscriptionRequest = new SubscriptionRequest();

		$this->addAddressDataFromRequest( $subscriptionRequest, $request );

		$subscriptionRequest->setEmail( $request->get( 'email', '' ) );

		$subscriptionRequest->setWikiloginFromValues( [
			$request->request->get( 'wikilogin' ),
			$request->cookies->get( 'spenden_wikilogin' ),
		] );

		$this->addTrackingDataFromRequest( $subscriptionRequest, $request );

		return $subscriptionRequest;
	}

	private function addAddressDataFromRequest( SubscriptionRequest $subscriptionRequest, Request $request ) {
		$subscriptionRequest->setAddress( $request->get( 'address', '' ) );
		$subscriptionRequest->setCity( $request->get( 'city', '' ) );
		$subscriptionRequest->setPostcode( $request->get( 'postcode', '' ) );

		$subscriptionRequest->setFirstName( $request->get( 'firstName', '' ) );
		$subscriptionRequest->setLastName( $request->get( 'lastName', '' ) );
		$subscriptionRequest->setSalutation( $request->get( 'salutation', '' ) );
		$subscriptionRequest->setTitle( $request->get( 'title', '' ) );

	}

	private function addTrackingDataFromRequest( SubscriptionRequest $subscriptionRequest, Request $request ) {
		$subscriptionRequest->setSource( $request->get( 'source', '' ) );
		$subscriptionRequest->setTrackingString( $request->attributes->get( 'trackingCode', '' ) );
	}

	private function handleHTML( Request $request, ValidationResponse $responseModel ): Response {
		// "normal" request to get the form on first go
		if ( $request->isMethod(Request::METHOD_GET) ) {
			return new Response(
				$this->ffFactory->getLayoutTemplate( 'Subscription_Form.html.twig' )->render( [] )
			);
		}

		if ( $responseModel->isSuccessful() ) {
			return $this->app->redirect( $this->app['url_generator']->generate('page', [
				'pageName' => $responseModel->needsModeration() ? 'Subscription_Moderation' : 'Subscription_Success'
			] ) );
		}

		return new Response(
			$this->ffFactory->newAddSubscriptionHtmlPresenter()->present( $responseModel, $request->request->all() )
		);
	}

	private function handleJSON( ValidationResponse $responseModel ): Response {
		return $this->app->json( $this->ffFactory->newAddSubscriptionJsonPresenter()->present( $responseModel ) );
	}

	private function handleJSONP( Request $request, ValidationResponse $responseModel ): Response {
		return $this->app->json( $this->ffFactory->newAddSubscriptionJsonPresenter()->present( $responseModel ) )
			->setCallback( $request->query->get( 'callback' ) );
	}
}