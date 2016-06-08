<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Domain;

use WMDE\Fundraising\Frontend\Domain\ReadModel\CommentWithAmount;

/**
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
interface CommentFinder {

	/**
	 * Returns the comments that can be shown to non-privileged users, newest first.
	 *
	 * @param int $limit
	 *
	 * @return CommentWithAmount[]
	 * @throws CommentListingException
	 */
	public function getPublicComments( int $limit ): array;

}
