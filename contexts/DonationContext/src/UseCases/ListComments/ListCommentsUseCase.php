<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\DonationContext\UseCases\ListComments;

use WMDE\Fundraising\Frontend\DonationContext\Domain\Repositories\CommentFinder;

/**
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class ListCommentsUseCase {

	private const MAX_PAGE = 100;
	private const MAX_LIMIT = 100;

	private $commentRepository;

	public function __construct( CommentFinder $commentRepository ) {
		$this->commentRepository = $commentRepository;
	}

	public function listComments( CommentListingRequest $listingRequest ): CommentList {
		$limit = $this->isValidLimit( $listingRequest->getLimit() ) ? $listingRequest->getLimit() : 10;
		$page = $this->isValidPageNumber( $listingRequest->getPage() ) ? $listingRequest->getPage() : 1;

		return new CommentList( ...$this->getListItems( $limit, $page ) );
	}

	private function isValidPageNumber( int $pageNumber ): bool {
		return $pageNumber <= self::MAX_PAGE && $pageNumber >= 1;
	}

	private function isValidLimit( int $limit ): bool {
		return $limit <= self::MAX_LIMIT && $limit >= 1;
	}

	private function getListItems( int $limit, int $page ): array {
		return $this->commentRepository->getPublicComments(
			$limit,
			( $page - 1 ) * $limit
		);
	}

}
