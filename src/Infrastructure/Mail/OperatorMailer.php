<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Infrastructure\Mail;

use WMDE\EmailAddress\EmailAddress;
use WMDE\Fundraising\Frontend\Presentation\TwigTemplate;

/**
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class OperatorMailer {

	private $messenger;
	private $template;

	public function __construct( Messenger $messenger, TwigTemplate $template ) {
		$this->messenger = $messenger;
		$this->template = $template;
	}

	/**
	 * @throws \RuntimeException
	 */
	public function sendMailToOperator( EmailAddress $replyToAddress, string $subject, array $templateArguments = [] ): void {
		$this->messenger->sendMessageToOperator(
			new Message(
				$subject,
				$this->template->render( $templateArguments )
			),
			$replyToAddress
		);
	}

}
