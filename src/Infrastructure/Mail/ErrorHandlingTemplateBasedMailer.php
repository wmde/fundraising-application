<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Infrastructure\Mail;

use Psr\Log\LoggerInterface;
use WMDE\EmailAddress\EmailAddress;
use WMDE\Fundraising\DonationContext\Infrastructure\TemplateMailerInterface as DonationTemplateMailerInterface;
use WMDE\Fundraising\MembershipContext\Infrastructure\TemplateMailerInterface as MembershipTemplateMailerInterface;

class ErrorHandlingTemplateBasedMailer implements DonationTemplateMailerInterface, MembershipTemplateMailerInterface {

	public function __construct(
		private readonly DonationTemplateMailerInterface|MembershipTemplateMailerInterface $templateBasedMailer,
		private readonly LoggerInterface $logger
	) {
	}

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
