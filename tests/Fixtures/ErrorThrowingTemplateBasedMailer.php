<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\Fixtures;

use WMDE\EmailAddress\EmailAddress;
use WMDE\Fundraising\DonationContext\Infrastructure\TemplateMailerInterface as DonationTemplateMailerInterface;
use WMDE\Fundraising\MembershipContext\Infrastructure\TemplateMailerInterface as MembershipTemplateMailerInterface;

class ErrorThrowingTemplateBasedMailer implements DonationTemplateMailerInterface, MembershipTemplateMailerInterface {

	private ?\Throwable $previous;

	public const ERROR_MESSAGE = "TO ERR IS HUMAN, BUT I IS ROBOT";

	public function __construct( ?\Throwable $previous = null ) {
		$this->previous = $previous;
	}

	public function sendMail( EmailAddress $recipient, array $templateArguments = [] ): void {
		throw new \RuntimeException( self::ERROR_MESSAGE, 0, $this->previous );
	}
}
