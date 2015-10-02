<?php

namespace WMDE\Fundraising\Frontend\UseCases\ListComments;

class CommentListItem {

	private $authorName;

	private $donationAmount;

	private $commentText;

	private $postingTime;

	/**
	 * @param string $authorName
	 * @param string $commentText
	 * @param float $donationAmount
	 * @param int $postingTime
	 */
	public function __construct( $authorName, $commentText, $donationAmount, $postingTime ) {
		$this->authorName = $authorName;
		$this->commentText = $commentText;
		$this->donationAmount = $donationAmount;
		$this->postingTime = $postingTime;
	}

	/**
	 * @return string
	 */
	public function getAuthorName() {
		return $this->authorName;
	}

	/**
	 * @return float
	 */
	public function getDonationAmount() {
		return $this->donationAmount;
	}

	/**
	 * @return string
	 */
	public function getCommentText() {
		return $this->commentText;
	}

	/**
	 * @return int
	 */
	public function getPostingTime() {
		return $this->postingTime;
	}

}
