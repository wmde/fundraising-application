<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\DonatingContext\UseCases\AddComment;

use WMDE\Fundraising\Frontend\FreezableValueObject;

/**
 * @license GNU GPL v2+
 * @author Gabriel Birke < gabriel.birke@wikimedia.de >
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class AddCommentRequest {
	use FreezableValueObject;

	private $commentText;
	private $isPublic;
	private $authorDisplayName;
	private $donationId;

	public function getCommentText(): string {
		return $this->commentText;
	}

	public function setCommentText( string $commentText ): self {
		$this->assertIsWritable();
		$this->commentText = $commentText;
		return $this;
	}

	public function isPublic(): bool {
		return $this->isPublic;
	}

	public function setIsPublic( bool $isPublic ): self {
		$this->assertIsWritable();
		$this->isPublic = $isPublic;
		return $this;
	}

	public function getAuthorDisplayName(): string {
		return $this->authorDisplayName;
	}

	public function setAuthorDisplayName( string $authorDisplayName ): self {
		$this->assertIsWritable();
		$this->authorDisplayName = $authorDisplayName;
		return $this;
	}

	public function getDonationId(): int {
		return $this->donationId;
	}

	public function setDonationId( int $donationId ): self {
		$this->assertIsWritable();
		$this->donationId = $donationId;
		return $this;
	}

}