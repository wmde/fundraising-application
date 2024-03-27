<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Presentation\Presenters;

use WMDE\Fundraising\DonationContext\Domain\ReadModel\Comment;
use WMDE\Fundraising\DonationContext\UseCases\ListComments\CommentList;

class CommentListJsonPresenter {

	/**
	 * @return array<array<string, float|int|string>>
	 */
	public function present( CommentList $commentList ): array {
		// TODO Translate keys into English once old skins are phased out
		return array_map(
			static function ( Comment $comment ) {
				return [
					'betrag' => $comment->donationAmount,
					'spender' => $comment->authorName,
					'kommentar' => $comment->commentText,
					'datum' => $comment->donationTime->format( 'r' ),
					'lokalisiertes_datum' => $comment->donationTime->format( 'd.m.Y \u\m H:i \U\h\r' ),
					'id' => $comment->donationId,
				];
			},
			$commentList->toArray()
		);
	}

}
