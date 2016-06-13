<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\UseCases\ListComments;

use WMDE\Fundraising\Frontend\Domain\CommentFinder;

/**
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class ListCommentsUseCase {

	private $commentRepository;

	public function __construct( CommentFinder $commentRepository ) {
		$this->commentRepository = $commentRepository;
	}

	public function listComments( CommentListingRequest $listingRequest ): CommentList {
		return new CommentList( ...$this->getListItems( $listingRequest ) );
	}

	private function getListItems( CommentListingRequest $listingRequest ): array {
		$offset = ( $listingRequest->getPage() - 1 ) * $listingRequest->getLimit();

		return $this->commentRepository->getPublicComments(
			$listingRequest->getLimit(),
			$offset
		);
	}

}
