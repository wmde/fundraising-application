<?php

declare(strict_types = 1);

namespace WMDE\Fundraising\Frontend\UseCases\ListComments;

use DateTime;

/**
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class CommentListItem {

	private $authorName;
	private $donationAmount;
	private $commentText;
	private $postingTime;

	public function __construct( string $authorName, string $commentText, float $donationAmount, DateTime $postingTime ) {
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

	public function getPostingTime(): DateTime {
		return $this->postingTime;
	}

}
