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
	private $operatorAddress;
	private $failedRecipients = [];

	public function __construct( Swift_Transport $mailTransport, MailAddress $operatorAddress ) {
		$this->mailTransport = $mailTransport;
		$this->operatorAddress = $operatorAddress;
	}

	public function constructMessage( MailAddress $sender, MailAddress $receiver,
		string $subject, string $body ): Swift_Message {

		$message = Swift_Message::newInstance( $subject, $body );
		$message->setFrom( $this->operatorAddress->getFullAddress(), $this->operatorAddress->getDisplayName() );
		$message->setTo( $receiver->getFullAddress(), $receiver->getDisplayName() );

		if ( $sender !== $this->operatorAddress ) {
			$message->setReplyTo( $sender->getFullAddress(), $sender->getDisplayName() );
		}

		return $message;
	}

	public function sendMessage( Swift_Mime_Message $message ) {
		$this->mailTransport->send( $message, $this->failedRecipients );
	}

	/**
	 * @return string[]
	 */
	public function getFailedRecipients(): array {
		return $this->failedRecipients;
	}

}
