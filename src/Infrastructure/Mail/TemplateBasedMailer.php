<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Infrastructure\Mail;

use WMDE\EmailAddress\EmailAddress;
use WMDE\Fundraising\DonationContext\Infrastructure\TemplateMailerInterface as DonationTemplateMailerInterface;
use WMDE\Fundraising\Frontend\Presentation\TwigTemplate;
use WMDE\Fundraising\MembershipContext\Infrastructure\TemplateMailerInterface as MembershipTemplateMailerInterface;
use WMDE\Fundraising\SubscriptionContext\Infrastructure\TemplateMailerInterface as SubscriptionTemplateMailerInterface;

class TemplateBasedMailer implements
	DonationTemplateMailerInterface,
	MembershipTemplateMailerInterface,
	SubscriptionTemplateMailerInterface,
	GetInTouchMailerInterface
{

	public function __construct(
		private readonly Messenger $messenger,
		private readonly TwigTemplate $template,
		private readonly MailSubjectRendererInterface $subjectRenderer
	) {
	}

	/**
	 * @inheritDoc
	 * @throws \RuntimeException
	 */
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
