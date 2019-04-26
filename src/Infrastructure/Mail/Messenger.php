<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Infrastructure\Mail;

use RuntimeException;
use Swift_Message;
use Swift_Transport;
use WMDE\EmailAddress\EmailAddress;

/**
 * @license GNU GPL v2+
 */
class Messenger {

	private $mailTransport;
	private $operatorAddress;
	private $operatorName;

	public function __construct( Swift_Transport $mailTransport,
								 EmailAddress $operatorAddress,
								 string $operatorName = '' ) {
		$this->mailTransport = $mailTransport;
		$this->operatorAddress = $operatorAddress;
		$this->operatorName = $operatorName;
	}

	/**
	 * @throws RuntimeException
	 */
	public function sendMessageToUser( Message $messageContent, EmailAddress $recipient ): void {
		$this->sendMessage( $this->createMessage( $messageContent, $recipient ) );
	}

	/**
	 * @throws RuntimeException
	 */
	public function sendMessageToOperator( Message $messageContent, EmailAddress $replyTo = null ): void {
		$this->sendMessage( $this->createMessage( $messageContent, $this->operatorAddress, $replyTo ) );
	}

	private function createMessage( Message $messageContent, EmailAddress $recipient,
									EmailAddress $replyTo = null ): Swift_Message {
		$message = Swift_Message::newInstance( $messageContent->getSubject(), $messageContent->getMessageBody() );
		$message->setFrom( $this->operatorAddress->getNormalizedAddress(), $this->operatorName );
		$message->setTo( $recipient->getNormalizedAddress() );

		if ( $replyTo ) {
			$message->setReplyTo( $replyTo->getNormalizedAddress() );
		}

		return $message;
	}

	private function sendMessage( Swift_Message $message ): void {
		$deliveryCount = $this->mailTransport->send( $message );
		if ( $deliveryCount === 0 ) {
			throw new MailerException( 'Message delivery failed' );
		}
	}

}
