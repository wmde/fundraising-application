<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Infrastructure;

use RuntimeException;
use Swift_Message;
use Swift_Transport;
use WMDE\Fundraising\Frontend\Domain\Model\EmailAddress;

/**
 * @licence GNU GPL v2+
 * @author Kai Nissen < kai.nissen@wikimedia.de >
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
	public function sendMessageToUser( Message $messageContent, EmailAddress $recipient ) {
		$this->sendMessage( $this->createMessage( $messageContent, $recipient ) );
	}

	/**
	 * @throws RuntimeException
	 */
	public function sendMessageToOperator( Message $messageContent, EmailAddress $replyTo = null ) {
		$this->sendMessage( $this->createMessage( $messageContent, $this->operatorAddress, $replyTo ) );
	}

	private function createMessage( Message $messageContent, EmailAddress $recipient,
									EmailAddress $replyTo = null ): Swift_Message {
		$message = Swift_Message::newInstance( $messageContent->getSubject(), $messageContent->getMessageBody() );
		$message->setFrom( $this->operatorAddress->getFullAddress(), $this->operatorName );
		$message->setTo( $recipient->getFullAddress() );
		if ( $replyTo ) {
			$message->setReplyTo( $replyTo->getFullAddress() );
		}

		return $message;
	}

	private function sendMessage( Swift_Message $message ) {
		$deliveryCount = $this->mailTransport->send( $message );
		if ( $deliveryCount === 0 ) {
			throw new RuntimeException( 'Message delivery failed' );
		}
	}

}
