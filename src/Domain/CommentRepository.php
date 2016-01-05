<?php

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
	 * @param int $limit
	 *
	 * @return Comment[]
	 */
	public function getComments( int $limit ): array;

}
