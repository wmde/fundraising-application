<?php

namespace WMDE\Fundraising\Frontend\Presenters;

use WMDE\Fundraising\Frontend\Domain\Comment;
use WMDE\Fundraising\Frontend\UseCases\ListComments\CommentList;

/**
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class CommentListJsonPresenter {

	public function present( CommentList $commentList ): array {
		return array_map(
			function( Comment $comment ) {
				return [
					'betrag' => $comment->getDonationAmount(),
					'spender' => $comment->getAuthorName(),
					'kommentar' => $comment->getCommentText(),
					'datum' => $comment->getPostingTime()->format( 'r' ),
					'id' => '',
				];
			},
			$commentList->toArray()
		);
	}

}