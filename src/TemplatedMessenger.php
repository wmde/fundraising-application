<?php


namespace WMDE\Fundraising\Frontend;

/**
 * @license GNU GPL v2+
 * @author Gabriel Birke < gabriel.birke@wikimedia.de >
 */
class TemplatedMessenger {

	private $messenger;
	private $subject;
	private $bodyTemplate;

	public function __construct( Messenger $messenger, string $subject, TwigTemplate $bodyTemplate ) {
		$this->messenger = $messenger;
		$this->subject = $subject;
		$this->bodyTemplate = $bodyTemplate;
	}

	public function sendMessage( array $templateData, MailAddress $recipient, MailAddress $replyTo = null ) {
		$this->messenger->sendMessage(
			$this->subject,
			$this->bodyTemplate->render( $templateData ),
			$recipient,
			$replyTo
		);
	}
}