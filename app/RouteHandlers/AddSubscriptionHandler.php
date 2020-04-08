<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\App\RouteHandlers;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
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

	private FunFunFactory $ffFactory;

	public function __construct( FunFunFactory $ffFactory ) {
		$this->ffFactory = $ffFactory;
	}

	public function handle( Request $request ): Response {

		$useCase = $this->ffFactory->newAddSubscriptionUseCase();
		$responseModel = $useCase->addSubscription( $this->createSubscriptionRequest( $request ) );

		if ( $request->query->has( 'callback' ) ) {
			return $this->handleJsonp( $request, $responseModel );
		} elseif ( $request->attributes->get( 'request_stack.is_json', false ) ) {
			return $this->handleJson( $responseModel );
		} else {
			return $this->handleHtml( $request, $responseModel );
		}
	}

	private function createSubscriptionRequest( Request $request ): SubscriptionRequest {
		$subscriptionRequest = new SubscriptionRequest();

		$subscriptionRequest->setEmail( $request->get( 'email', '' ) );
		$subscriptionRequest->setSource( $request->get( 'source', '' ) );
		$subscriptionRequest->setTrackingString( $request->attributes->get( 'trackingCode', '' ) );

		return $subscriptionRequest;
	}

	private function handleHtml( Request $request, ValidationResponse $responseModel ): Response {
		// "normal" request to get the form on first go
		if ( $request->isMethod( Request::METHOD_GET ) ) {
			return new Response(
				$this->ffFactory->getLayoutTemplate( 'Subscription_Form.html.twig' )->render( [] )
			);
		}

		if ( $responseModel->isSuccessful() ) {
			return new RedirectResponse( $this->ffFactory->getUrlGenerator()->generateAbsoluteUrl( 'page', [
				'pageName' => $responseModel->needsModeration() ? 'Subscription_Moderation' : 'Subscription_Success'
			] ) );
		}

		return new Response(
			$this->ffFactory->newAddSubscriptionHtmlPresenter()->present( $responseModel, $request->request->all() )
		);
	}

	private function handleJson( ValidationResponse $responseModel ): JsonResponse {
		return JsonResponse::create( $this->ffFactory->newAddSubscriptionJsonPresenter()->present( $responseModel ) );
	}

	private function handleJsonp( Request $request, ValidationResponse $responseModel ): Response {
		return $this->handleJson( $responseModel )->setCallback( $request->query->get( 'callback' ) );
	}
}