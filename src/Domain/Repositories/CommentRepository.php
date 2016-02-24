<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Domain\Repositories;

use WMDE\Fundraising\Frontend\Domain\Model\Comment;

/**
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
interface CommentRepository {

	/**
	 * @param Comment $comment
	 * @throws StoreCommentException
	 */
	public function storeComment( Comment $comment );

}
