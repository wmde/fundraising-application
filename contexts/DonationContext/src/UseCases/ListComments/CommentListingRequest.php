<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\DonationContext\UseCases\ListComments;

/**
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class CommentListingRequest {

	const FIRST_PAGE = 1;

	private $limit;
	private $page;

	public function __construct( int $limit, int $page ) {
		$this->limit = $limit;
		$this->page = $page;
	}

	public function getLimit(): int {
		return $this->limit;
	}

	public function getPage(): int {
		return $this->page;
	}

}
