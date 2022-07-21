<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\App\Controllers\Donation;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use WMDE\Fundraising\DonationContext\UseCases\AddComment\AddCommentRequest;
use WMDE\Fundraising\Frontend\Factories\FunFunFactory;

class AddCommentController {

	/**
	 * @todo Expose this text as a public constant in AddCommentUseCase instead
	 */
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
		$addCommentRequest = new AddCommentRequest();
		$addCommentRequest->setCommentText( trim( $postVars->get( 'comment', '' ) ) );
		$addCommentRequest->setIsPublic( $postVars->getBoolean( 'public' ) );
		$addCommentRequest->setDonationId( (int)$postVars->get( 'donationId', '' ) );

		if ( $postVars->getBoolean( 'isAnonymous' ) ) {
			$addCommentRequest->setIsAnonymous();
		} else {
			$addCommentRequest->setIsNamed();
		}

		$addCommentRequest->freeze()->assertNoNullFields();
		return $addCommentRequest;
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
