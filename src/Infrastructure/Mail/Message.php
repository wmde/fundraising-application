<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Infrastructure\Mail;

class Message {

	public function __construct(
		private readonly string $subject,
		private readonly string $messageBody
	) {
	}

	public function getSubject(): string {
		return $this->subject;
	}

	public function getMessageBody(): string {
		return $this->messageBody;
	}

}
