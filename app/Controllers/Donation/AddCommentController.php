<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\App\Controllers\Donation;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use WMDE\Fundraising\DonationContext\UseCases\AddComment\AddCommentRequest;
use WMDE\Fundraising\Frontend\Factories\FunFunFactory;

/**
 * @deprecated Use API\AddCommentController instead and delete this controller and its route when the frontend is no longer using it.
 */
class AddCommentController {

	private const ACCESS_DENIED_MSG = 'comment_failure_access_denied';

	public function index( FunFunFactory $ffFactory, Request $request ): Response {
		$postVars = $request->request;
		$addCommentRequest = $this->buildAddCommentRequest( $postVars );

		$updateToken = $postVars->get( 'updateToken', '' );
		if ( $updateToken === '' ) {
			return $this->newErrorResponse( self::ACCESS_DENIED_MSG );
		}

		$response = $ffFactory->newAddCommentUseCase( $updateToken )->addComment( $addCommentRequest );

		if ( $response->isSuccessful() ) {
			return new JsonResponse(
				[
					'status' => 'OK',
					'message' => $response->getSuccessMessage(),
				]
			);
		}

		return $this->newErrorResponse( $response->getErrorMessage() );
	}

	private function buildAddCommentRequest( ParameterBag $postVars ): AddCommentRequest {
		return new AddCommentRequest(
			commentText: trim( $postVars->get( 'comment', '' ) ),
			isPublic: $postVars->getBoolean( 'isPublic' ),
			isAnonymous: $postVars->getBoolean( 'withName' ),
			donationId: (int)$postVars->get( 'donationId', '' ),
		);
	}

	private function newErrorResponse( string $message ): Response {
		return new JsonResponse(
			[
				'status' => 'ERR',
				'message' => $message,
			],
			$message === self::ACCESS_DENIED_MSG ? Response::HTTP_FORBIDDEN : Response::HTTP_OK
		);
	}

}
