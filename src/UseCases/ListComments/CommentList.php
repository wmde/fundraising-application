<?php

namespace WMDE\Fundraising\Frontend\UseCases\ListComments;

class CommentList {

	private $comments;

	/**
	 * @param ...$comments CommentListItem[]
	 */
	public function __construct( ...$comments ) {
		$this->comments = $comments;
	}

	/**
	 * @return CommentListItem[]
	 */
	public function toArray(): array {
		return $this->comments;
	}

}
