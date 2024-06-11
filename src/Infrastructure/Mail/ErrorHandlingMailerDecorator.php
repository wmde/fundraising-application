<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Infrastructure\Mail;

use Psr\Log\LoggerInterface;
use WMDE\EmailAddress\EmailAddress;

class ErrorHandlingMailerDecorator implements TemplateMailerInterface {

	public function __construct(
		private readonly TemplateMailerInterface $templateBasedMailer,
		private readonly LoggerInterface $logger
	) {
	}

	/**
	 * @param EmailAddress $recipient
	 * @param array<string, mixed> $templateArguments
	 */
	public function sendMail( EmailAddress $recipient, array $templateArguments = [] ): void {
		try {
			$this->templateBasedMailer->sendMail( $recipient, $templateArguments );
		} catch ( \RuntimeException $e ) {
			$message = $e->getMessage();
			$exception = $e;
			$previous = $e->getPrevious();
			if ( $previous !== null ) {
				$message .= ' ' . $previous->getMessage();
				$exception = $previous;
			}
			$this->logger->error( $message, [ 'exception' => $exception ] );
		}
	}
}
