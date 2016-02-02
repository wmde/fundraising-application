<?php

declare(strict_types = 1);

namespace WMDE\Fundraising\Frontend;

use Swift_Message;
use Swift_Transport;

/**
 * @licence GNU GPL v2+
 * @author Kai Nissen < kai.nissen@wikimedia.de >
 */
class Messenger {

	private $mailTransport;
	private $operatorAddress;
	private $operatorName;

	public function __construct( Swift_Transport $mailTransport,
								 MailAddress $operatorAddress,
								 string $operatorName = '' ) {
		$this->mailTransport = $mailTransport;
		$this->operatorAddress = $operatorAddress;
		$this->operatorName = $operatorName;
	}

	public function sendMessage( Message $messageContent, MailAddress $recipient ) {
		$message = Swift_Message::newInstance( $messageContent->getSubject(), $messageContent->getMessageBody() );
		$message->setFrom( $this->operatorAddress->getFullAddress(), $this->operatorName );
		$message->setTo( $recipient->getFullAddress() );

		$deliveryCount = $this->mailTransport->send( $message );
		if ( $deliveryCount === 0 ) {
			throw new \RuntimeException( 'Message delivery failed' );
		}
	}

	public function sendMessageToOperator( Message $messageContent, MailAddress $replyTo = null ) {
		$message = Swift_Message::newInstance( $messageContent->getSubject(), $messageContent->getMessageBody() );
		$message->setFrom( $this->operatorAddress->getFullAddress(), $this->operatorName );
		$message->setTo( $this->operatorAddress->getFullAddress() );
		if ( $replyTo ) {
			$message->setReplyTo( $replyTo->getFullAddress() );
		}

		$deliveryCount = $this->mailTransport->send( $message );
		if ( $deliveryCount === 0 ) {
			throw new \RuntimeException( 'Message delivery failed' );
		}
	}

}
