<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\MembershipContext\Infrastructure;

use WMDE\EmailAddress\EmailAddress;

interface TemplateMailerInterface {

	/**
	 * @param EmailAddress $recipient The recipient of the email to send
	 * @param array $templateArguments Context parameters to use while rendering the template
	 */
	public function sendMail( EmailAddress $recipient, array $templateArguments = [] ): void;
}
