<?php

namespace WMDE\Fundraising\Frontend\UseCases\ListComments;

/**
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class CommentListingRequest {

	public $limit;

	public function __construct( int $limit ) {
		$this->limit = $limit;
	}

	public function getLimit(): int {
		return $this->limit;
	}

}
