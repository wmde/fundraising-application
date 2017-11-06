<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\Unit\Infrastructure;

use Swift_NullTransport;
use WMDE\EmailAddress\EmailAddress;
use WMDE\Fundraising\Frontend\Infrastructure\Message;
use WMDE\Fundraising\Frontend\Infrastructure\Messenger;

/**
 * @covers WMDE\Fundraising\Frontend\Infrastructure\Messenger
 *
 * @licence GNU GPL v2+
 * @author Kai Nissen < kai.nissen@wikimedia.de >
 */
class MessengerTest extends \PHPUnit\Framework\TestCase {

	public function testWhenSendReturnsZero_exceptionIsThrown(): void {
		$mailTransport = $this->newMailTransport();

		$mailTransport->expects( $this->once() )
			->method( 'send' )
			->willReturn( 0 );

		$this->expectException( \RuntimeException::class );
		$this->expectExceptionMessage( 'Message delivery failed' );
		( new Messenger( $mailTransport, new EmailAddress( 'hostmaster@thatoperator.com' ) ) )
			->sendMessageToUser(
				new Message( 'Test message', 'This is just a test' ),
				new EmailAddress( 'i.want@to.receive.com' )
			);
	}

	private function newMailTransport(): Swift_NullTransport {
		return $this->getMockBuilder( Swift_NullTransport::class )
			->disableOriginalConstructor()
			->getMock();
	}

	public function testSendToAddressWithInternationalCharacters_doesNotCauseException(): void {
		$messenger = new Messenger(
			$this->newMailTransport(),
			new EmailAddress( 'hostmaster@thatoperator.com' )
		);

		$messenger->sendMessageToUser(
			new Message( 'Test message', 'Test content' ),
			new EmailAddress( 'info@müllerrr.de' )
		);

		$this->assertTrue( true );
	}

}
