<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Presentation\Presenters;

use WMDE\Fundraising\DonationContext\Domain\Repositories\CommentWithAmount;
use WMDE\Fundraising\DonationContext\UseCases\ListComments\CommentList;
use WMDE\Fundraising\Frontend\Presentation\TwigTemplate;

class CommentListHtmlPresenter {

	public function __construct( private readonly TwigTemplate $template ) {
	}

	public function present( CommentList $commentList, int $pageNumber ): string {
		return $this->template->render( [
			'comments' => array_map(
				static function ( CommentWithAmount $comment ) {
					return [
						'amount' => $comment->getDonationAmount(),
						'author' => $comment->getAuthorName(),
						'text' => $comment->getCommentText(),
						'publicationDate' => $comment->getDonationTime()->format( 'r' ),
					];
				},
				$commentList->toArray()
			),
			'page' => $pageNumber,
			'max_pages' => 100
		] );
	}

}
