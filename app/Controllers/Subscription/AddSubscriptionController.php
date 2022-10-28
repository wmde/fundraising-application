<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\App\Controllers\Subscription;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use WMDE\Fundraising\Frontend\App\EventHandlers\AddIndicatorAttributeForJsonRequests;
use WMDE\Fundraising\Frontend\Factories\FunFunFactory;
use WMDE\Fundraising\Frontend\Presentation\Presenters\AddSubscriptionJsonPresenter;
use WMDE\Fundraising\SubscriptionContext\UseCases\AddSubscription\SubscriptionRequest;
use WMDE\FunValidators\ValidationResponse;

class AddSubscriptionController {

	public function index( Request $request, FunFunFactory $ffFactory ): Response {
		$useCase = $ffFactory->newAddSubscriptionUseCase();
		$responseModel = $useCase->addSubscription( $this->createSubscriptionRequest( $request ) );

		if ( $request->attributes->get( AddIndicatorAttributeForJsonRequests::REQUEST_IS_JSON_ATTRIBUTE, false ) ) {
			return $this->createJsonResponse(
				$responseModel,
				$ffFactory->newAddSubscriptionJsonPresenter(),
				$request->query->get( 'callback', '' ),
			);
		} elseif ( $request->getMethod() === 'GET' ) {
			return new Response( 'Bad request - method GET only allowed with JSONP callback.', Response::HTTP_BAD_REQUEST );
		}

		// We don't check the $responseModel further because we don't want to bother users too much.
		// We only validate the email address and if it does not validate, we still return success.
		return new RedirectResponse( $ffFactory->getUrlGenerator()->generateRelativeUrl( 'page', [ 'pageName' => 'Subscription_Success' ] ) );
	}

	private function createSubscriptionRequest( Request $request ): SubscriptionRequest {
		$subscriptionRequest = new SubscriptionRequest();

		$subscriptionRequest->setEmail( $request->get( 'email', '' ) );
		$subscriptionRequest->setSource( $request->get( 'source', '' ) );
		$subscriptionRequest->setTrackingString( $request->attributes->get( 'trackingCode', '' ) );

		return $subscriptionRequest;
	}

	private function createJsonResponse( ValidationResponse $responseModel, AddSubscriptionJsonPresenter $presenter, ?string $callback = '' ): Response {
		$response = new JsonResponse( $presenter->present( $responseModel ) );
		// set JsonP callback
		if ( $callback ) {
			$response->setCallback( $callback );
		}
		return $response;
	}
}
