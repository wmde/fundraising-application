<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\UseCases\ListComments;

/**
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class CommentListingRequest {

	private $limit;

	public function __construct( int $limit ) {
		$this->limit = $limit;
	}

	public function getLimit(): int {
		return $this->limit;
	}

}
