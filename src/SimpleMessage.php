<?php

declare(strict_types = 1);

namespace WMDE\Fundraising\Frontend;

/**
 * @licence GNU GPL v2+
 * @author Kai Nissen < kai.nissen@wikimedia.de >
 */
class SimpleMessage implements Message {

	private $subject;
	private $messageBody;

	public function __construct( string $subject, string $messageBody ) {
		$this->subject = $subject;
		$this->messageBody = $messageBody;
	}

	public function getSubject(): string {
		return $this->subject;
	}

	public function getMessageBody(): string {
		return $this->messageBody;
	}

}
