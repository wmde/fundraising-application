<?php

declare(strict_types = 1);

namespace WMDE\Fundraising\Frontend\Domain;

/**
 * TODO: it is not clear at present that we gain much by having this additional
 * repository layer between the usecase and the data access implementation.
 *
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
