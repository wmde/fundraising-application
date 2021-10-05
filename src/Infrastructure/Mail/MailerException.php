<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Infrastructure\Mail;

/**
 * @license GPL-2.0-or-later
 */
class MailerException extends \RuntimeException {

	public function __construct( string $message, \Throwable $previous = null ) {
		parent::__construct( $message, 0, $previous );
	}

}
