<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Infrastructure\Mail;

use WMDE\EmailAddress\EmailAddress;
use WMDE\Fundraising\DonationContext\Infrastructure\TemplateMailerInterface as DonationTemplateMailerInterface;
use WMDE\Fundraising\Frontend\Presentation\TwigTemplate;
use WMDE\Fundraising\SubscriptionContext\Infrastructure\TemplateMailerInterface as SubscriptionTemplateMailerInterface;

/**
 * @license GPL-2.0-or-later
 */
interface MailSubjectRendererInterface {

	public function render( array $templateArguments = [] ): string;

}
