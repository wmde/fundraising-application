<?php

declare(strict_types = 1);

namespace WMDE\Fundraising\Frontend\Domain\Model;

use WMDE\Fundraising\Frontend\FreezableValueObject;

/**
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class Comment {
	use FreezableValueObject;

	private $authorDisplayName;
	private $commentText;
	private $donationId;
	private $isPublic;

	public function getAuthorDisplayName(): string {
		return $this->authorDisplayName;
	}

	public function getCommentText(): string {
		return $this->commentText;
	}

	public function getDonationId(): int {
		return $this->donationId;
	}

	public function setAuthorDisplayName( string $authorDisplayName ) {
		$this->assertIsWritable();
		$this->authorDisplayName = $authorDisplayName;
	}

	public function setCommentText( string $commentText ) {
		$this->assertIsWritable();
		$this->commentText = $commentText;
	}

	public function setDonationId( int $donationId ) {
		$this->assertIsWritable();
		$this->donationId = $donationId;
	}

	public function isPublic(): bool {
		return $this->isPublic;
	}

	public function setIsPublic( bool $isPublic ) {
		$this->assertIsWritable();
		$this->isPublic = $isPublic;
	}

}
