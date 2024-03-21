<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Infrastructure\Mail;

class MailerException extends \RuntimeException {

	public function __construct(
		string $message,
		\Throwable $previous = null
	) {
		parent::__construct( $message, 0, $previous );
	}

}
