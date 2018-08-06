<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Infrastructure;

use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
use WMDE\EmailAddress\EmailAddress;
use WMDE\Fundraising\MembershipContext\Infrastructure\TemplateMailerInterface;
use WMDE\Fundraising\DonationContext\Infrastructure\TemplateMailerInterface as DonationTemplateMailerInterface;
use WMDE\Fundraising\SubscriptionContext\Infrastructure\TemplateMailerInterface as SubscriptionTemplateMailerInterface;

/**
 * @license GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class LoggingMailer implements
	TemplateMailerInterface,
	DonationTemplateMailerInterface,
	SubscriptionTemplateMailerInterface {

	const CONTEXT_EXCEPTION_KEY = 'exception';

	private $mailer;
	private $logger;
	private $logLevel;

	public function __construct( TemplateMailerInterface $mailer, LoggerInterface $logger ) {
		$this->mailer = $mailer;
		$this->logger = $logger;
		$this->logLevel = LogLevel::CRITICAL;
	}

	/**
	 * @inheritdoc
	 * @throws \RuntimeException
	 */
	public function sendMail( EmailAddress $recipient, array $templateArguments = [] ): void {
		try {
			$this->mailer->sendMail( $recipient, $templateArguments );
		}
		catch ( \RuntimeException $ex ) {
			$this->logger->log( $this->logLevel, $ex->getMessage(), [ self::CONTEXT_EXCEPTION_KEY => $ex ] );
			throw $ex;
		}
	}
}
