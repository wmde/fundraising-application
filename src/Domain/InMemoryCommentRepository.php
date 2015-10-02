<?php

namespace WMDE\Fundraising\Frontend\Domain;

class InMemoryCommentRepository implements CommentRepository {

	private $comments;

	/**
	 * @param Comment[] $comments
	 */
	public function __construct( array $comments ) {
		$this->comments = $comments;
	}

	/**
	 * @param int $limit
	 *
	 * @return Comment[]
	 */
	public function getComments( $limit ) {
		return array_slice( $this->comments, 0, $limit );
	}

}
