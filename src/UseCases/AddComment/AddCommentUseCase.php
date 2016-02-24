<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\UseCases\AddComment;

use WMDE\Fundraising\Frontend\Domain\Model\Comment;
use WMDE\Fundraising\Frontend\Domain\Repositories\CommentRepository;
use WMDE\Fundraising\Frontend\Domain\Repositories\StoreCommentException;

/**
 * @license GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class AddCommentUseCase {

	private $commentRepository;

	public function __construct( CommentRepository $repository ) {
		$this->commentRepository = $repository;
	}

	public function addComment( AddCommentRequest $addCommentRequest ): AddCommentResponse {
		$comment = $this->newCommentFromRequest( $addCommentRequest );

		try {
			$this->commentRepository->storeComment( $comment );
		}
		catch ( StoreCommentException $ex ) {
			return AddCommentResponse::newFailureResponse( 'Could not add comment' );
		}

		return AddCommentResponse::newSuccessResponse();
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