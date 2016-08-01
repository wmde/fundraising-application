<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\Fixtures;

use WMDE\Fundraising\Frontend\DonatingContext\Domain\Repositories\CommentFinder;
use WMDE\Fundraising\Frontend\DonatingContext\Domain\Repositories\CommentWithAmount;

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
	 * @param int $offset
	 *
	 * @return \WMDE\Fundraising\Frontend\DonatingContext\Domain\Repositories\CommentWithAmount[]
	 */
	public function getPublicComments( int $limit, int $offset = 0 ): array {
		return array_slice( $this->comments, $offset, $limit );
	}

}
