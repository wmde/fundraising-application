<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\App\Controllers\API\Donation;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use WMDE\Fundraising\Frontend\Factories\FunFunFactory;

class AddCommentController extends AbstractApiController {

	private const ACCESS_DENIED_MSG = 'comment_failure_no_update_token';

	public function index( FunFunFactory $ffFactory, #[MapRequestPayload] FrameworkAddCommentRequest $request ): Response {
		$updateToken = $request->updateToken;
		if ( $updateToken === '' ) {
			return $this->errorResponse( self::ACCESS_DENIED_MSG, Response::HTTP_FORBIDDEN );
		}

		$response = $ffFactory->newAddCommentUseCase( $updateToken )->addComment( $request->getRequestForUseCase() );

		if ( $response->isSuccessful() ) {
			return new JsonResponse(
				[
					'status' => 'OK',
					'message' => $response->getSuccessMessage(),
				]
			);
		}

		return $this->errorResponse( $response->getErrorMessage(), Response::HTTP_OK );
	}

}
