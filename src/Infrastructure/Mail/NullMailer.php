<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Infrastructure\Mail;

use WMDE\EmailAddress\EmailAddress;
use WMDE\Fundraising\DonationContext\Infrastructure\TemplateMailerInterface;

class NullMailer implements TemplateMailerInterface {

	public function sendMail( EmailAddress $recipient, array $templateArguments = [] ): void {
		// Does nothing on purpose
	}
}
