<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Infrastructure;

use WMDE\EmailAddress\EmailAddress;
use WMDE\Fundraising\Frontend\Presentation\TwigTemplate;
use WMDE\Fundraising\DonationContext\Infrastructure\TemplateMailerInterface as DonationTemplateMailerInterface;
use WMDE\Fundraising\SubscriptionContext\Infrastructure\TemplateMailerInterface as SubscriptionTemplateMailerInterface;

/**
 * @license GNU GPL v2+
 */
class TemplateBasedMailer implements DonationTemplateMailerInterface, SubscriptionTemplateMailerInterface,
	GetInTouchMailerInterface {

	private $messenger;
	private $template;
	private $subject;

	public function __construct( Messenger $messenger, TwigTemplate $template, string $mailSubject ) {
		$this->messenger = $messenger;
		$this->template = $template;
		$this->subject = $mailSubject;
	}

	/**
	 * @inheritdoc
	 * @throws \RuntimeException
	 */
	public function sendMail( EmailAddress $recipient, array $templateArguments = [] ): void {
		$this->messenger->sendMessageToUser(
			new Message(
				$this->subject,
				MailFormatter::format( $this->template->render( $templateArguments ) )
			),
			$recipient
		);
	}

}
