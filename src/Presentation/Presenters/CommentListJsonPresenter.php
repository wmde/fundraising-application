<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Presentation\Presenters;

use WMDE\Fundraising\DonationContext\Domain\Repositories\CommentWithAmount;
use WMDE\Fundraising\DonationContext\UseCases\ListComments\CommentList;

/**
 * @license GPL-2.0-or-later
 */
class CommentListJsonPresenter {

	public function present( CommentList $commentList ): array {
		// TODO Translate keys into English once old skins are phased out
		return array_map(
			static function ( CommentWithAmount $comment ) {
				return [
					'betrag' => $comment->getDonationAmount(),
					'spender' => $comment->getAuthorName(),
					'kommentar' => $comment->getCommentText(),
					'datum' => $comment->getDonationTime()->format( 'r' ),
					'lokalisiertes_datum' => $comment->getDonationTime()->format( 'd.m.Y \u\m H:i \U\h\r' ),
					'id' => $comment->getDonationId(),
				];
			},
			$commentList->toArray()
		);
	}

}
