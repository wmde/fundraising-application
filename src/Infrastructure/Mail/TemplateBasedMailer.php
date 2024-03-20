<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Infrastructure\Mail;

use WMDE\EmailAddress\EmailAddress;
use WMDE\Fundraising\Frontend\Presentation\TwigTemplate;
use WMDE\Fundraising\SubscriptionContext\Infrastructure\TemplateMailerInterface as SubscriptionTemplateMailerInterface;

/**
 * This is a class that sends e-mails with contents based on the Twig template passed in the constructor.
 */
class TemplateBasedMailer implements
	SubscriptionTemplateMailerInterface,
	GetInTouchMailerInterface,
	TemplateMailerInterface
{

	public function __construct(
		private readonly Messenger $messenger,
		private readonly TwigTemplate $template,
		private readonly MailSubjectRendererInterface $subjectRenderer
	) {
	}

	public function sendMail( EmailAddress $recipient, array $templateArguments = [] ): void {
		$this->messenger->sendMessageToUser(
			new Message(
				$this->subjectRenderer->render( $templateArguments ),
				MailFormatter::format( $this->template->render( $templateArguments ) )
			),
			$recipient
		);
	}

}
