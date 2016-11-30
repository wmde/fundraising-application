<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\App\RouteHandlers;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use WMDE\Fundraising\Frontend\Factories\FunFunFactory;
use WMDE\Fundraising\Frontend\SubscriptionContext\UseCases\AddSubscription\SubscriptionRequest;

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

		if ( $this->app['request_stack.is_json'] || $this->app['request_stack.is_jsonp'] ) {
			return $this->app->json( $this->ffFactory->newAddSubscriptionJSONPresenter()->present( $responseModel ) );
		}

		if ( $responseModel->isSuccessful() ) {
			if ( $responseModel->needsModeration() ) {
				return $this->app->redirect( $this->app['url_generator']->generate('page', [ 'pageName' => 'Subscription_Moderation' ] ) );
			}
			return $this->app->redirect( $this->app['url_generator']->generate('page', [ 'pageName' => 'Subscription_Success' ] ) );
		}

		return new Response(
			$this->ffFactory->newAddSubscriptionHTMLPresenter()->present( $responseModel, $request->request->all() )
		);
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
}