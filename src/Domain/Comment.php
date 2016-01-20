<?php

declare(strict_types = 1);

namespace WMDE\Fundraising\Frontend\Domain;

use DateTime;
use WMDE\Fundraising\Frontend\FreezableValueObject;

/**
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class Comment {
	use FreezableValueObject;

	private $authorName;
	private $donationAmount;
	private $commentText;
	private $postingTime;

	public static function newInstance() {
		return new self();
	}

	private function __construct() {
	}

	public function getAuthorName(): string {
		return $this->authorName;
	}

	public function getDonationAmount(): float {
		return $this->donationAmount;
	}

	public function getCommentText(): string {
		return $this->commentText;
	}

	public function getPostingTime(): DateTime {
		return $this->postingTime;
	}

	public function setAuthorName( string $authorName ): self {
		$this->assertIsWritable();
		$this->authorName = $authorName;
		return $this;
	}

	public function setDonationAmount( float $donationAmount ): self {
		$this->assertIsWritable();
		$this->donationAmount = $donationAmount;
		return $this;
	}

	public function setCommentText( string $commentText ): self {
		$this->assertIsWritable();
		$this->commentText = $commentText;
		return $this;
	}

	public function setPostingTime( DateTime $postingTime ): self {
		$this->assertIsWritable();
		$this->postingTime = $postingTime;
		return $this;
	}

}
