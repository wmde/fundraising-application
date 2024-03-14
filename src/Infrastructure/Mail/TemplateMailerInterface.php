<?php
declare( strict_types=1 );

namespace WMDE\Fundraising\Frontend\Infrastructure\Mail;

use WMDE\EmailAddress\EmailAddress;

/**
 * This is an interface that enables the different wrappers for
 */
interface TemplateMailerInterface {
	public function sendMail( EmailAddress $recipient, array $templateArguments = [] ): void;
}
