<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Infrastructure;

use WMDE\EmailAddress\EmailAddress;
use WMDE\Fundraising\Frontend\Presentation\TwigTemplate;

/**
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class OperatorMailer {

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
	public function sendMailToOperator( EmailAddress $replyToAddress, array $templateArguments = [] ): void {
		$this->messenger->sendMessageToOperator(
			new Message(
				$this->subject,
				$this->template->render( $templateArguments )
			),
			$replyToAddress
		);
	}

}
