<?php
declare( strict_types=1 );

namespace WMDE\Fundraising\Frontend\Infrastructure\Mail;

use WMDE\EmailAddress\EmailAddress;
use WMDE\Fundraising\DonationContext\Infrastructure\DonationNotifier\TemplateArgumentsDonationConfirmation;
use WMDE\Fundraising\DonationContext\Infrastructure\DonorNotificationInterface;

class DonationConfirmationMailerAdapter implements DonorNotificationInterface {
	public function __construct(
		private readonly TemplateMailerInterface $templateMailer
	) {
	}

	public function sendMail( EmailAddress $recipient, TemplateArgumentsDonationConfirmation $templateArguments ): void {
		$this->templateMailer->sendMail( $recipient, [
			'recipient' => $templateArguments->recipient,
			'donation' => get_object_vars( $templateArguments->donation )
		] );
	}
}
