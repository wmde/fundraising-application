<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\DonationContext\UseCases\ListComments;

use WMDE\Fundraising\Frontend\DonationContext\Domain\Repositories\CommentWithAmount;

/**
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class CommentList {

	private $comments;

	public function __construct( CommentWithAmount ...$comments ) {
		$this->comments = $comments;
	}

	/**
	 * @return CommentWithAmount[]
	 */
	public function toArray(): array {
		return $this->comments;
	}

}
