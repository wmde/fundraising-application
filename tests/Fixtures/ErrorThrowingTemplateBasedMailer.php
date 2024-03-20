<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\Fixtures;

use WMDE\EmailAddress\EmailAddress;
use WMDE\Fundraising\Frontend\Infrastructure\Mail\TemplateMailerInterface;

class ErrorThrowingTemplateBasedMailer implements TemplateMailerInterface {

	public const ERROR_MESSAGE = "TO ERR IS HUMAN, BUT I IS ROBOT";

	public function __construct( private readonly ?\Throwable $previous = null ) {
	}

	public function sendMail( EmailAddress $recipient, array $templateArguments = [] ): void {
		throw new \RuntimeException( self::ERROR_MESSAGE, 0, $this->previous );
	}
}
