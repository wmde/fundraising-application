<?php

namespace WMDE\Fundraising\Frontend\Presenters;

use WMDE\Fundraising\Frontend\TwigTemplate;
use WMDE\Fundraising\Frontend\UseCases\DisplayPage\PageDisplayResponse;
use WMDE\Fundraising\Frontend\UseCases\ListComments\CommentList;
use WMDE\Fundraising\Frontend\UseCases\ListComments\CommentListItem;

/**
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class CommentListJsonPresenter {

	public function present( CommentList $commentList ): array {
		return array_map(
			function( CommentListItem $commentListItem ) {
				return [
					'betrag' => $commentListItem->getDonationAmount(),
					'spender' => $commentListItem->getAuthorName(),
					'kommentar' => $commentListItem->getCommentText(),
					'datum' => $commentListItem->getPostingTime()->format( 'r' ),
					'id' => '',
				];
			},
			$commentList->toArray()
		);
	}

}