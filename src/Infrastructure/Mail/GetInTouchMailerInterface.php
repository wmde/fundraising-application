<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Infrastructure\Mail;

use WMDE\EmailAddress\EmailAddress;

interface GetInTouchMailerInterface {

	/**
	 * @param EmailAddress $recipient The recipient of the email to send
	 * @param array<string, mixed> $templateArguments Context parameters to use while rendering the template
	 */
	public function sendMail( EmailAddress $recipient, array $templateArguments = [] ): void;
}
#
