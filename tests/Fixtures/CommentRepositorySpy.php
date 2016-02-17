<?php

declare(strict_types = 1);

namespace WMDE\Fundraising\Frontend\Tests\Fixtures;

use WMDE\Fundraising\Frontend\Domain\Model\Comment;
use WMDE\Fundraising\Frontend\Domain\Repositories\CommentRepository;
use WMDE\Fundraising\Frontend\Domain\Repositories\StoreCommentException;

/**
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class CommentRepositorySpy implements CommentRepository {

	private $storeCommentCalls = [];

	/**
	 * @param Comment $comment
	 *
	 * @throws StoreCommentException
	 */
	public function storeComment( Comment $comment ) {
		$this->storeCommentCalls[] = $comment;
	}

	/**
	 * @return Comment[]
	 */
	public function getStoreCommentCalls(): array {
		return $this->storeCommentCalls;
	}

}
