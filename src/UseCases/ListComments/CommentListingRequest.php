<?php

namespace WMDE\Fundraising\Frontend\UseCases\ListComments;

class CommentListingRequest {

	public $limit;

	/**
	 * @param int $limit
	 */
	public function __construct( $limit ) {
		$this->limit = $limit;
	}

	public function getLimit() {
		return $this->limit;
	}

}
