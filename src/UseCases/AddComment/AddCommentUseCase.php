<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\UseCases\AddComment;

use WMDE\Fundraising\Frontend\Domain\Model\Comment;
use WMDE\Fundraising\Frontend\Domain\Repositories\CommentRepository;
use WMDE\Fundraising\Frontend\Domain\Repositories\StoreCommentException;
use WMDE\Fundraising\Frontend\Infrastructure\AuthorizationChecker;

/**
 * @license GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class AddCommentUseCase {

	private $commentRepository;
	private $authorizationService;

	public function __construct( CommentRepository $repository, AuthorizationChecker $authorizationService ) {
		$this->commentRepository = $repository;
		$this->authorizationService = $authorizationService;
	}

	public function addComment( AddCommentRequest $addCommentRequest ): AddCommentResponse {
		if ( !$this->requestIsAllowed( $addCommentRequest ) ) {
			return AddCommentResponse::newFailureResponse( 'Authorization failed' );
		}

		$comment = $this->newCommentFromRequest( $addCommentRequest );

		try {
			$this->commentRepository->storeComment( $comment );
		}
		catch ( StoreCommentException $ex ) {
			return AddCommentResponse::newFailureResponse( 'Could not add comment' );
		}

		return AddCommentResponse::newSuccessResponse();
	}

	private function requestIsAllowed( AddCommentRequest $addCommentRequest ): bool {
		return $this->authorizationService->canModifyDonation( $addCommentRequest->getDonationId() );
	}

	private function newCommentFromRequest( AddCommentRequest $request ): Comment {
		$comment = new Comment();

		$comment->setAuthorDisplayName( $request->getAuthorDisplayName() );
		$comment->setCommentText( $request->getCommentText() );
		$comment->setDonationId( $request->getDonationId() );
		$comment->setIsPublic( $request->isPublic() );

		return $comment->freeze()->assertNoNullFields();
	}

}