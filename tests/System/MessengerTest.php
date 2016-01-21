<?php

namespace WMDE\Fundraising\Frontend\Tests\Unit;

use Swift_Message;
use WMDE\Fundraising\Frontend\MailAddress;
use WMDE\Fundraising\Frontend\Messenger;

/**
 * @covers WMDE\Fundraising\Frontend\Messenger
 *
 * @licence GNU GPL v2+
 * @author Kai Nissen < kai.nissen@wikimedia.de >
 */
class MessengerTest extends \PHPUnit_Framework_TestCase {

	/** @var Messenger */
	private $messenger;

	public function testWhenSenderDiffersFromOperator_composeMessageAddsReplyToHeader() {
		$subject = 'Test message';
		$body = 'This is just a test';

		$operatorAddress = new MailAddress( 'hostmaster@thatoperator.com', 'Friendly Operator' );
		$receiverAddress = new MailAddress( 'i.want@to.receive.com', 'I\'m a receiver' );
		$senderAddress = new MailAddress( 'i.want@to.send.com', 'I\'m a sender' );

		$this->messenger = new Messenger( new \Swift_NullTransport(), $operatorAddress );
		$expected = $this->createExpectedMessage( $operatorAddress, $senderAddress, $receiverAddress, $subject, $body );
		$actual = $this->createActualMessage( $senderAddress, $receiverAddress, $subject, $body );

		$this->assertEquals( $expected->getReplyTo(), $actual->getReplyTo() );
		$this->assertEquals( $expected->getFrom(), $actual->getFrom() );
	}

	public function testWhenDeliverySucceeds_getFailedRecipientsReturnsEmptyArray() {
		$subject = 'Test message';
		$body = 'This is just a test';

		$operatorAddress = new MailAddress( 'hostmaster@thatoperator.com', 'Friendly Operator' );
		$receiverAddress = new MailAddress( 'i.want@to.receive.com', 'I\'m a receiver' );
		$senderAddress = new MailAddress( 'i.want@to.send.com', 'I\'m a sender' );

		$this->messenger = new Messenger( new \Swift_NullTransport(), $operatorAddress );
		$actual = $this->createActualMessage( $senderAddress, $receiverAddress, $subject, $body );
		$this->messenger->sendMessage( $actual );

		$this->assertEquals( [], $this->messenger->getFailedRecipients() );
	}

	private function createExpectedMessage( MailAddress $operatorAddress, MailAddress $sender, MailAddress $receiver,
											string $subject, string $body ) {
		$message = new Swift_Message( $subject, $body );
		$message->setFrom( $operatorAddress->getNormalizedAddress(), $operatorAddress->getDisplayName() );
		$message->setTo( $receiver->getNormalizedAddress(), $receiver->getDisplayName() );
		$message->setReplyTo( $sender->getNormalizedAddress(), $sender->getDisplayName() );
		return $message;
	}

	private function createActualMessage( MailAddress $sender, MailAddress $receiver,
										  string $subject, string $body ) {
		return $this->messenger->constructMessage( $sender, $receiver, $subject, $body );
	}
}
