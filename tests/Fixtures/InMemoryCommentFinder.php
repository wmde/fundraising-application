<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\Fixtures;

use WMDE\Fundraising\Frontend\Domain\CommentFinder;
use WMDE\Fundraising\Frontend\Domain\ReadModel\CommentWithAmount;

/**
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class InMemoryCommentFinder implements CommentFinder {

	private $comments;

	public function __construct( CommentWithAmount ...$comments ) {
		$this->comments = $comments;
	}

	/**
	 * @param int $limit
	 *
	 * @return CommentWithAmount[]
	 */
	public function getPublicComments( int $limit ): array {
		return array_slice( $this->comments, 0, $limit );
	}

}
