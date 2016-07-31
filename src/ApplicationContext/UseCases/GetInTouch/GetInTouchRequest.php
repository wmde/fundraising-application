<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\ApplicationContext\UseCases\GetInTouch;

/**
 * @license GNU GPL v2+
 * @author Kai Nissen < kai.nissen@wikimedia.de >
 */
class GetInTouchRequest {

	private $firstName;
	private $lastName;
	private $emailAddress;
	private $subject;
	private $messageBody;

	public function __construct( string $firstName, string $lastName, string $emailAddress,
								 string $subject, string $messageBody ) {
		$this->firstName = $firstName;
		$this->lastName = $lastName;
		$this->emailAddress = $emailAddress;
		$this->subject = $subject;
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

	public function getSubject(): string {
		return $this->subject;
	}

	public function getMessageBody(): string {
		return $this->messageBody;
	}

}
