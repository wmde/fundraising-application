<?php

namespace WMDE\Fundraising\Frontend\Domain;

interface CommentRepository {

	/**
	 * @param int $limit
	 *
	 * @return Comment[]
	 */
	public function getComments( int $limit ): array;

}
