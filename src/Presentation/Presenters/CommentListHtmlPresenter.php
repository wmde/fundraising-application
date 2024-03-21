<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Presentation\Presenters;

use WMDE\Fundraising\DonationContext\Domain\ReadModel\Comment;
use WMDE\Fundraising\DonationContext\UseCases\ListComments\CommentList;
use WMDE\Fundraising\Frontend\Presentation\TwigTemplate;

class CommentListHtmlPresenter {

	public function __construct( private readonly TwigTemplate $template ) {
	}

	public function present( CommentList $commentList, int $pageNumber ): string {
		return $this->template->render( [
			'comments' => array_map(
				static function ( Comment $comment ) {
					return [
						'amount' => $comment->donationAmount,
						'author' => $comment->authorName,
						'text' => $comment->commentText,
						'publicationDate' => $comment->donationTime->format( 'r' ),
					];
				},
				$commentList->toArray()
			),
			'page' => $pageNumber,
			'max_pages' => 100
		] );
	}

}
