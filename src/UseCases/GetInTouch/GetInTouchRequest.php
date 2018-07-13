<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\UseCases\GetInTouch;

/**
 * @license GNU GPL v2+
 * @author Kai Nissen < kai.nissen@wikimedia.de >
 */
class GetInTouchRequest {

	private $firstName;
	private $lastName;
	private $emailAddress;
	private $donationNumber;
	private $subject;
	private $category;
	private $messageBody;

	public function __construct( string $firstName, string $lastName, string $emailAddress,
								 string $donationNumber, string $subject, string $category, string $messageBody ) {
		$this->firstName = $firstName;
		$this->lastName = $lastName;
		$this->emailAddress = $emailAddress;
		$this->donationNumber = $donationNumber;
		$this->subject = $subject;
		$this->category = $category;
		$this->messageBody = $messageBody;
	}

	public function getFirstName(): string {
		return $this->firstName;
	}

	public function getLastName(): string {
		return $this->lastName;
	}

	public function getEmailAddress(): string {
		return $this->emailAddress;
	}

	public function getDonationNumber(): string {
		return $this->donationNumber;
	}

	public function getSubject(): string {
		return $this->subject;
	}

	public function getCategory(): string {
		return $this->category;
	}

	public function getMessageBody(): string {
		return $this->messageBody;
	}

}
