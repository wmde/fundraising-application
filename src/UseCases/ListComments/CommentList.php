<?php

namespace WMDE\Fundraising\Frontend\UseCases\ListComments;

/**
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
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
