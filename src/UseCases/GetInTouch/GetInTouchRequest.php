<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\UseCases\GetInTouch;

class GetInTouchRequest {

	public function __construct(
		private readonly string $firstName,
		private readonly string $lastName,
		private readonly string $emailAddress,
		private readonly string $donationNumber,
		private readonly string $subject,
		private readonly string $category,
		private readonly string $messageBody
	) {
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
