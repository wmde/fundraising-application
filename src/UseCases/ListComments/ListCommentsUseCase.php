<?php

namespace WMDE\Fundraising\Frontend\UseCases\ListComments;

use WMDE\Fundraising\Frontend\Domain\Comment;
use WMDE\Fundraising\Frontend\Domain\CommentRepository;

class ListCommentsUseCase {

	private $presenter;

	private $commentRepository;

	public function __construct( CommentListPresenter $presenter, CommentRepository $commentRepository ) {
		$this->presenter = $presenter;
		$this->commentRepository = $commentRepository;
	}

	public function listComments( CommentListingRequest $listingRequest ) {
		$this->presenter->listComments( new CommentList( ...$this->getListItems( $listingRequest ) ) );
	}

	private function getListItems( CommentListingRequest $listingRequest ): array {
		return array_map(
			function( Comment $comment ) {
				return new CommentListItem(
					$comment->getAuthorName(),
					$comment->getCommentText(),
					$comment->getDonationAmount(),
					$comment->getPostingTime()
				);
			},
			$this->commentRepository->getComments( $listingRequest->getLimit() )
		);
	}

}
