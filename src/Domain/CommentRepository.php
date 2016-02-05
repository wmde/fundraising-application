<?php

declare(strict_types = 1);

namespace WMDE\Fundraising\Frontend\Domain;

/**
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
interface CommentRepository {

	/**
	 * Returns the comments that can be shown to non-privileged users, newest first.
	 *
	 * @param int $limit
	 *
	 * @return Comment[]
	 */
	public function getPublicComments( int $limit ): array;

}
