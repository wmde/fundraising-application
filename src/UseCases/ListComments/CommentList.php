<?php

declare(strict_types = 1);

namespace WMDE\Fundraising\Frontend\UseCases\ListComments;

use WMDE\Fundraising\Frontend\Domain\Comment;

/**
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class CommentList {

	private $comments;

	public function __construct( Comment ...$comments ) {
		$this->comments = $comments;
	}

	/**
	 * @return Comment[]
	 */
	public function toArray(): array {
		return $this->comments;
	}

}
