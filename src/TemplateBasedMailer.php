<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend;

use WMDE\Fundraising\Frontend\Domain\Model\MailAddress;

/**
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class TemplateBasedMailer {

	private $messenger;
	private $template;
	private $subject;

	public function __construct( Messenger $messenger, TwigTemplate $template, string $mailSubject ) {
		$this->messenger = $messenger;
		$this->template = $template;
		$this->subject = $mailSubject;
	}

	/**
	 * @throws \RuntimeException
	 */
	public function sendMail( MailAddress $recipient, array $templateArguments = [] ) {
		$this->messenger->sendMessageToUser(
			new SimpleMessage(
				$this->subject,
				$this->template->render( $templateArguments )
			),
			$recipient
		);
	}

}
