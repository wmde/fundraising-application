<?php

namespace WMDE\Fundraising\Frontend\Tests\Unit;

use Swift_Message;
use Swift_Mime_SimpleMessage;
use Swift_NullTransport;
use WMDE\Fundraising\Frontend\Domain\Model\MailAddress;
use WMDE\Fundraising\Frontend\Messenger;
use WMDE\Fundraising\Frontend\SimpleMessage;

/**
 * @covers WMDE\Fundraising\Frontend\Messenger
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
				new SimpleMessage( 'Test message', 'This is just a test' ),
				new MailAddress( 'i.want@to.receive.com' )
			);
	}

}
