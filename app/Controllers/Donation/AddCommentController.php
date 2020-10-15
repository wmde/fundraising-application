<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\App\Controllers\Donation;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use WMDE\Fundraising\DonationContext\UseCases\AddComment\AddCommentRequest;
use WMDE\Fundraising\Frontend\Factories\FunFunFactory;

class AddCommentController {

	public function handle( FunFunFactory $ffFactory, Request $request ): Response {
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
}
