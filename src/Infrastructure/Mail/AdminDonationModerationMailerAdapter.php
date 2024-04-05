<?php
declare( strict_types=1 );

namespace WMDE\Fundraising\Frontend\Infrastructure\Mail;

use WMDE\EmailAddress\EmailAddress;
use WMDE\Fundraising\DonationContext\Infrastructure\AdminNotificationInterface;
use WMDE\Fundraising\DonationContext\Infrastructure\DonationNotifier\TemplateArgumentsAdmin;

class AdminDonationModerationMailerAdapter implements AdminNotificationInterface {
	public function __construct(
		private readonly TemplateMailerInterface $templateMailer
	) {
	}

	public function sendMail( EmailAddress $recipient, TemplateArgumentsAdmin $templateArguments ): void {
		$this->templateMailer->sendMail(
			$recipient,
			[
				'id' => $templateArguments->donationId,
				'moderationFlags' => $templateArguments->moderationFlags,
				'amount' => $templateArguments->amount
			]
		);
	}
}
