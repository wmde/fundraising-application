<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Presentation\Presenters;

use WMDE\Fundraising\Frontend\DonationContext\Domain\Repositories\CommentWithAmount;
use WMDE\Fundraising\Frontend\DonationContext\UseCases\ListComments\CommentList;
use WMDE\Fundraising\Frontend\Presentation\TwigTemplate;

/**
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class CommentListRssPresenter {

	private $template;

	public function __construct( TwigTemplate $template ) {
		$this->template = $template;
	}

	public function present( CommentList $commentList ): string {
		return $this->template->render( [
			'rssPublicationDate' => $this->getPublicationTime( $commentList ),
			'comments' => $this->getCommentsViewModel( $commentList ),
		] );
	}

	private function getCommentsViewModel( CommentList $commentList ) {
		return array_map(
			function( CommentWithAmount $comment ) {
				return [
					'amount' => $comment->getDonationAmount(),
					'author' => $comment->getAuthorName(),
					'text' => $comment->getCommentText(),
					'publicationDate' => $comment->getDonationTime()->format( 'r' ),
				];
			},
			$commentList->toArray()
		);
	}

	private function getPublicationTime( CommentList $commentList ) {
		if ( !array_key_exists( 0, $commentList->toArray() ) ) {
			return '';
		}

		return $commentList->toArray()[0]->getDonationTime()->format( 'r' );
	}

}
