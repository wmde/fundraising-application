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
	private $recipient;

	public function __construct( Messenger $messenger, string $subject, TwigTemplate $bodyTemplate, MailAddress $recipient ) {
		$this->messenger = $messenger;
		$this->subject = $subject;
		$this->bodyTemplate = $bodyTemplate;
		$this->recipient = $recipient;
	}

	public function sendMessage( array $templateData, MailAddress $replyTo = null ) {
		$this->messenger->sendMessage(
			$this->subject,
			$this->bodyTemplate->render( $templateData ),
			$this->recipient,
			$replyTo
		);
	}
}