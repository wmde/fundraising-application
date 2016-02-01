<?php


namespace WMDE\Fundraising\Frontend\Tests\Unit;

use WMDE\Fundraising\Frontend\TemplatedMessenger;
use WMDE\Fundraising\Frontend\Messenger;
use WMDE\Fundraising\Frontend\TwigTemplate;
use WMDE\Fundraising\Frontend\MailAddress;

class TemplatedMessengerTest extends \PHPUnit_Framework_TestCase {

	public function testItSendsMailsWithMessengerAndTemplate() {
		$template = $this->getMockBuilder( TwigTemplate::class )->disableOriginalConstructor()->getMock();
		$messenger = $this->getMockBuilder( Messenger::class )->disableOriginalConstructor()->getMock();
		$recipient = $this->getMockBuilder( MailAddress::class )->disableOriginalConstructor()->getMock();
		$replyTo = $this->getMockBuilder( MailAddress::class )->disableOriginalConstructor()->getMock();
		$templatedMessenger  = new TemplatedMessenger( $messenger, 'Important unicorn update', $template, $recipient );
		$template->method( 'render' )->willReturn( 'Pink fluffy unicorns dancing on rainbows' );
		$messenger->expects( $this->once() )
			->method( 'sendMessage' )
			->with( 'Important unicorn update', 'Pink fluffy unicorns dancing on rainbows', $recipient, $replyTo );
		$templatedMessenger->sendMessage( [], $recipient, $replyTo );
	}
}
