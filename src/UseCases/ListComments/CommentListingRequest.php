<?php

namespace WMDE\Fundraising\Frontend\UseCases\ListComments;

class CommentListingRequest {

	public $limit;

	public function __construct( int $limit ) {
		$this->limit = $limit;
	}

	public function getLimit(): int {
		return $this->limit;
	}

}
