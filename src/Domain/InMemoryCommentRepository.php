<?php

namespace WMDE\Fundraising\Frontend\Domain;

/**
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class InMemoryCommentRepository implements CommentRepository {

	private $comments;

	public function __construct( Comment ...$comments ) {
		$this->comments = $comments;
	}

	/**
	 * @param int $limit
	 *
	 * @return Comment[]
	 */
	public function getComments( int $limit ): array {
		return array_slice( $this->comments, 0, $limit );
	}

}
