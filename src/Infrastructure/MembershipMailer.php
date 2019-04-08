<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Infrastructure;

use WMDE\EmailAddress\EmailAddress;
use WMDE\Fundraising\Frontend\Presentation\TwigTemplate;
use WMDE\Fundraising\MembershipContext\Infrastructure\TemplateMailerInterface;

/**
 * @license GNU GPL v2+
 */
class MembershipMailer implements TemplateMailerInterface {

	private $messenger;
	private $template;
	private $activeMembershipSubject;
	private $sustainingMembershipSubject;

	public function __construct(
		Messenger $messenger,
		TwigTemplate $template,
		string $activeMembershipSubject,
		string $sustainingMembershipSubject ) {
		$this->messenger = $messenger;
		$this->template = $template;
		$this->activeMembershipSubject = $activeMembershipSubject;
		$this->sustainingMembershipSubject = $sustainingMembershipSubject;
	}

	/**
	 * @inheritdoc
	 * @throws \RuntimeException
	 */
	public function sendMail( EmailAddress $recipient, bool $isActiveMembership, array $templateArguments = [] ): void {
		$this->messenger->sendMessageToUser(
			new Message(
				$isActiveMembership ? $this->activeMembershipSubject : $this->sustainingMembershipSubject,
				MailFormatter::format( $this->template->render( $templateArguments ) )
			),
			$recipient
		);
	}

}
