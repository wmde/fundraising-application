<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Infrastructure;

use WMDE\EmailAddress\EmailAddress;
use WMDE\Fundraising\Frontend\Presentation\TwigTemplate;
use WMDE\Fundraising\MembershipContext\Infrastructure\TemplateMailerInterface;
use WMDE\Fundraising\DonationContext\Infrastructure\TemplateMailerInterface as DonationTemplateMailerInterface;
use WMDE\Fundraising\SubscriptionContext\Infrastructure\TemplateMailerInterface as SubscriptionTemplateMailerInterface;

/**
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class TemplateBasedMailer implements TemplateMailerInterface, DonationTemplateMailerInterface,
	SubscriptionTemplateMailerInterface {

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
				$this->template->render( $templateArguments )
			),
			$recipient
		);
	}

}
