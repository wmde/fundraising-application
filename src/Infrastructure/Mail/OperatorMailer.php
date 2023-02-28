<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Infrastructure\Mail;

use WMDE\EmailAddress\EmailAddress;
use WMDE\Fundraising\Frontend\Presentation\TwigTemplate;

class OperatorMailer {

	public function __construct(
		private readonly Messenger $messenger,
		private readonly TwigTemplate $template
	) {
	}

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
