<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\Unit\Infrastructure;

use Swift_NullTransport;
use WMDE\Fundraising\Frontend\Domain\Model\MailAddress;
use WMDE\Fundraising\Frontend\Infrastructure\Messenger;
use WMDE\Fundraising\Frontend\Infrastructure\Message;

/**
 * @covers WMDE\Fundraising\Frontend\Infrastructure\Messenger
 *
 * @licence GNU GPL v2+
 * @author Kai Nissen < kai.nissen@wikimedia.de >
 */
class MessengerTest extends \PHPUnit_Framework_TestCase {

	public function testWhenSendReturnsZero_exceptionIsThrown() {
		$mailTransport = $this->getMockBuilder( Swift_NullTransport::class )
			->disableOriginalConstructor()
			->getMock();

		$mailTransport->expects( $this->once() )
			->method( 'send' )
			->willReturn( 0 );

		$this->expectException( \RuntimeException::class );
		$this->expectExceptionMessage( 'Message delivery failed' );
		( new Messenger( $mailTransport, new MailAddress( 'hostmaster@thatoperator.com' ) ) )
			->sendMessageToUser(
				new Message( 'Test message', 'This is just a test' ),
				new MailAddress( 'i.want@to.receive.com' )
			);
	}

}
