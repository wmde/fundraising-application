<?php

namespace WMDE\Fundraising\Frontend\UseCases\ListComments;

/**
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class CommentList {

	private $comments;

	public function __construct( CommentListItem ...$comments ) {
		$this->comments = $comments;
	}

	/**
	 * @return CommentListItem[]
	 */
	public function toArray(): array {
		return $this->comments;
	}

}
