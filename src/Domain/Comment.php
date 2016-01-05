<?php

namespace WMDE\Fundraising\Frontend\Domain;

class Comment {

	private $authorName;
	private $donationAmount;
	private $commentText;
	private $postingTime;

	public function __construct( string $authorName, string $commentText, float $donationAmount, int $postingTime ) {
		$this->authorName = $authorName;
		$this->commentText = $commentText;
		$this->donationAmount = $donationAmount;
		$this->postingTime = $postingTime;
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

	public function getPostingTime(): int {
		return $this->postingTime;
	}

}
