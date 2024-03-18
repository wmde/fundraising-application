<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Infrastructure\Mail;

use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;
use WMDE\EmailAddress\EmailAddress;

class Messenger {

	private MailerInterface $mailer;
	private EmailAddress $operatorAddress;
	private string $operatorName;

	public function __construct( MailerInterface $mailer,
								 EmailAddress $operatorAddress,
								 string $operatorName = '' ) {
		$this->mailer = $mailer;
		$this->operatorAddress = $operatorAddress;
		$this->operatorName = $operatorName;
	}

	public function sendMessageToUser( Message $messageContent, EmailAddress $recipient ): void {
		$this->sendMessage( $this->createMessage( $messageContent, $recipient ) );
	}

	public function sendMessageToOperator( Message $messageContent, EmailAddress $replyTo = null ): void {
		$this->sendMessage( $this->createMessage( $messageContent, $this->operatorAddress, $replyTo ) );
	}

	private function createMessage( Message $messageContent, EmailAddress $recipient,
									EmailAddress $replyTo = null ): Email {
		$message = new Email();
		$message
			->text( $messageContent->getMessageBody() )
			->subject( $messageContent->getSubject() )
			->from( new Address( $this->operatorAddress->getNormalizedAddress(), $this->operatorName ) )
			->to( new Address( $recipient->getNormalizedAddress() ) );

		if ( $replyTo ) {
			$message->replyTo( new Address( $replyTo->getNormalizedAddress() ) );
		}

		return $message;
	}

	private function sendMessage( Email $message ): void {
		try {
			$this->mailer->send( $message );
		} catch ( TransportExceptionInterface $e ) {
			throw new MailerException( 'Message delivery failed', $e );
		}
	}

}
