<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\Unit\Infrastructure\Mail;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DoesNotPerformAssertions;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Mailer\Exception\TransportException;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mailer\Transport\NullTransport;
use Symfony\Component\Mailer\Transport\TransportInterface;
use WMDE\EmailAddress\EmailAddress;
use WMDE\Fundraising\Frontend\Infrastructure\Mail\MailerException;
use WMDE\Fundraising\Frontend\Infrastructure\Mail\Message;
use WMDE\Fundraising\Frontend\Infrastructure\Mail\Messenger;

#[CoversClass( Messenger::class )]
class MessengerTest extends TestCase {

	public function testItWrapsTransportExceptions(): void {
		$transportException = new TransportException( "The drone crashed" );
		$mailTransport = $this->createMock( TransportInterface::class );
		$mailTransport->expects( $this->once() )
			->method( 'send' )
			->willThrowException( $transportException );

		try {
			( new Messenger( new Mailer( $mailTransport ), new EmailAddress( 'hostmaster@thatoperator.com' ) ) )
				->sendMessageToUser(
					new Message( 'Test message', 'This is just a test' ),
					new EmailAddress( 'i.want@to.receive.com' )
				);
		} catch ( MailerException $e ) {
			$this->assertSame( 'Message delivery failed', $e->getMessage() );
			$this->assertSame( $transportException, $e->getPrevious() );
			return;
		}
		$this->fail( 'Messenger did not throw MailerException' );
	}

	#[DoesNotPerformAssertions]
	public function testSendToAddressWithInternationalCharacters_doesNotCauseException(): void {
		$messenger = new Messenger(
			new Mailer( new NullTransport() ),
			new EmailAddress( 'hostmaster@thatoperator.com' )
		);

		$messenger->sendMessageToUser(
			new Message( 'Test message', 'Test content' ),
			new EmailAddress( 'info@müllerrr.de' )
		);
	}

}
