<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\App\Controllers;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use WMDE\Fundraising\DonationContext\UseCases\AddComment\AddCommentRequest;
use WMDE\Fundraising\Frontend\Factories\FunFunFactory;

class AddCommentController {

	public function addComment( FunFunFactory $ffFactory, Request $request ): Response {
		$addCommentRequest = new AddCommentRequest();
		$addCommentRequest->setCommentText( trim( $request->request->get( 'comment', '' ) ) );
		$addCommentRequest->setIsPublic( $request->request->getBoolean( 'public' ) );
		$addCommentRequest->setDonationId( (int)$request->request->get( 'donationId', '' ) );

		if ( $request->request->getBoolean( 'isAnonymous' ) ) {
			$addCommentRequest->setIsAnonymous();
		} else {
			$addCommentRequest->setIsNamed();
		}

		$addCommentRequest->freeze()->assertNoNullFields();

		$updateToken = $request->request->get( 'updateToken', '' );

		if ( $updateToken === '' ) {
			return JsonResponse::create(
				[
					'status' => 'ERR',
					'message' => 'comment_failure_access_denied',
				]
			);
		}

		$response = $ffFactory->newAddCommentUseCase( $updateToken )->addComment( $addCommentRequest );

		if ( $response->isSuccessful() ) {
			return JsonResponse::create(
				[
					'status' => 'OK',
					'message' => $response->getSuccessMessage(),
				]
			);
		}

		return JsonResponse::create(
			[
				'status' => 'ERR',
				'message' => $response->getErrorMessage(),
			]
		);
	}

	public function viewComment( FunFunFactory $ffFactory, Request $request ): Response {
		$template = $ffFactory->getLayoutTemplate(
			'Donation_Comment.html.twig'
		);

		return new Response(
			$template->render(
				[
					'donationId' => (int)$request->query->get( 'donationId', '' ),
					'updateToken' => $request->query->get( 'updateToken', '' ),
					'cancelUrl' => $ffFactory->getUrlGenerator()->generateRelativeUrl(
						'show-donation-confirmation',
						[
							'id' => (int)$request->query->get( 'donationId', '' ),
							'accessToken' => $request->query->get( 'accessToken', '' )
						]
					)
				]
			)
		);
	}
}
