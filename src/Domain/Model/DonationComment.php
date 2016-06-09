<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Domain\Model;

/**
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class DonationComment {

	private $commentText;
	private $isPublic;
	private $authorDisplayName;

	public function __construct( string $commentText, bool $isPublic, string $authorDisplayName ) {
		$this->commentText = $commentText;
		$this->isPublic = $isPublic;
		$this->authorDisplayName = $authorDisplayName;
	}

	public function getAuthorDisplayName(): string {
		return $this->authorDisplayName;
	}

	public function getCommentText(): string {
		return $this->commentText;
	}

	public function isPublic(): bool {
		return $this->isPublic;
	}

}
