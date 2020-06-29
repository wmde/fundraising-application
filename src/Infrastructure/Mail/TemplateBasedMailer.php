<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Infrastructure\Mail;

use WMDE\EmailAddress\EmailAddress;
use WMDE\Fundraising\DonationContext\Infrastructure\TemplateMailerInterface as DonationTemplateMailerInterface;
use WMDE\Fundraising\Frontend\Presentation\TwigTemplate;
use WMDE\Fundraising\MembershipContext\Infrastructure\TemplateMailerInterface as MembershipTemplateMailerInterface;
use WMDE\Fundraising\SubscriptionContext\Infrastructure\TemplateMailerInterface as SubscriptionTemplateMailerInterface;

/**
 * @license GPL-2.0-or-later
 */
class TemplateBasedMailer implements
	DonationTemplateMailerInterface,
	MembershipTemplateMailerInterface,
	SubscriptionTemplateMailerInterface,
	GetInTouchMailerInterface
{

	private $messenger;
	private $template;
	private $subjectRenderer;

	public function __construct( Messenger $messenger, TwigTemplate $template, MailSubjectRendererInterface $subjectRenderer ) {
		$this->messenger = $messenger;
		$this->template = $template;
		$this->subjectRenderer = $subjectRenderer;
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
