<?php

namespace WMDE\Fundraising\Frontend\Tests\Unit;

use Swift_Message;
use Swift_Mime_SimpleMessage;
use Swift_NullTransport;
use WMDE\Fundraising\Frontend\MailAddress;
use WMDE\Fundraising\Frontend\Messenger;

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

		$this->setExpectedException( \RuntimeException::class );
		( new Messenger( $mailTransport, new MailAddress( 'hostmaster@thatoperator.com' ) ) )
			->sendMessage(
				'Test message',
				'This is just a test',
				new MailAddress( 'i.want@to.receive.com' )
			);
	}

}
