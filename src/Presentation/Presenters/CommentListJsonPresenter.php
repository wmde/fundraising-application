<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Presentation\Presenters;

use WMDE\Fundraising\DonationContext\Domain\Repositories\CommentWithAmount;
use WMDE\Fundraising\DonationContext\UseCases\ListComments\CommentList;

/**
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class CommentListJsonPresenter {

	public function present( CommentList $commentList ): array {
		return array_map(
			function( CommentWithAmount $comment ) {
				return [
					'betrag' => $comment->getDonationAmount(),
					'spender' => $comment->getAuthorName(),
					'kommentar' => $comment->getCommentText(),
					'datum' => $comment->getDonationTime()->format( 'r' ),
					'id' => $comment->getDonationId(),
				];
			},
			$commentList->toArray()
		);
	}

}