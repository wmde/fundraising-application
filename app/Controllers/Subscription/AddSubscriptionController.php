<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\App\Controllers\Subscription;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use WMDE\Fundraising\Frontend\Factories\FunFunFactory;
use WMDE\Fundraising\Frontend\Presentation\Presenters\AddSubscriptionHtmlPresenter;
use WMDE\Fundraising\Frontend\Presentation\Presenters\AddSubscriptionJsonPresenter;
use WMDE\Fundraising\SubscriptionContext\UseCases\AddSubscription\SubscriptionRequest;
use WMDE\FunValidators\ValidationResponse;

class AddSubscriptionController {

	public function index( Request $request, FunFunFactory $ffFactory ): Response {
		$useCase = $ffFactory->newAddSubscriptionUseCase();
		$responseModel = $useCase->addSubscription( $this->createSubscriptionRequest( $request ) );

		if ( $request->attributes->get( 'request_stack.is_json', false ) ) {
			return $this->createJsonResponse(
				$responseModel,
				$ffFactory->newAddSubscriptionJsonPresenter(),
				$request->query->get( 'callback', '' ),
			);
		}

		return $this->createHtmlResponse(
			$request,
			$responseModel,
			$ffFactory->newAddSubscriptionHtmlPresenter(),
			$ffFactory->getUrlGenerator()->generateRelativeUrl( 'page', [ 'pageName' => 'Subscription_Success' ] )
		);
	}

	private function createSubscriptionRequest( Request $request ): SubscriptionRequest {
		$subscriptionRequest = new SubscriptionRequest();

		$subscriptionRequest->setEmail( $request->get( 'email', '' ) );
		$subscriptionRequest->setSource( $request->get( 'source', '' ) );
		$subscriptionRequest->setTrackingString( $request->attributes->get( 'trackingCode', '' ) );

		return $subscriptionRequest;
	}

	private function createHtmlResponse( Request $request, ValidationResponse $responseModel, AddSubscriptionHtmlPresenter $presenter, string $successUrl ): Response {
		// GET request will display the form, we don't care about the $responseModel in this case
		if ( $request->isMethod( Request::METHOD_GET ) ) {
			return new Response( $presenter->present( ValidationResponse::newSuccessResponse(), [] ) );
		}

		// Redirect to success page
		if ( $responseModel->isSuccessful() ) {
			return new RedirectResponse( $successUrl );
		}

		// Re-display the form with errors
		return new Response(
			$presenter->present( $responseModel, $request->request->all() )
		);
	}

	private function createJsonResponse( ValidationResponse $responseModel, AddSubscriptionJsonPresenter $presenter, ?string $callback = '' ): Response {
		$response = JsonResponse::create( $presenter->present( $responseModel ) );
		// set JsonP callback
		if ( $callback ) {
			$response->setCallback( $callback );
		}
		return $response;
	}
}
