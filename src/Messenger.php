<?php

declare(strict_types = 1);

namespace WMDE\Fundraising\Frontend;

use Swift_Message;
use Swift_Mime_Message;
use Swift_Transport;

/**
 * @licence GNU GPL v2+
 * @author Kai Nissen < kai.nissen@wikimedia.de >
 */
class Messenger {

	private $mailTransport;
	private $failedRecipients = [];

	public function __construct( Swift_Transport $mailTransport ) {
		$this->mailTransport = $mailTransport;
	}

	public function constructMessage( MailAddress $sender, MailAddress $receiver, string $subject, string $body ) {
		$message = Swift_Message::newInstance( $subject, $body );
		$message->setFrom( $sender->getFullAddress(), $sender->getDisplayName() );
		$message->setTo( $receiver->getFullAddress(), $receiver->getDisplayName() );

		return $message;
	}

	public function sendMessage( Swift_Mime_Message $message ) {
		$this->mailTransport->send( $message, $this->failedRecipients );
	}

	public function getFailedRecipients() {
		return $this->failedRecipients;
	}

}
