<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Infrastructure;

use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
use WMDE\Fundraising\Frontend\MembershipContext\Domain\Model\EmailAddress;

/**
 * @license GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class LoggingMailer extends TemplateBasedMailer {

	const CONTEXT_EXCEPTION_KEY = 'exception';

	private $mailer;
	private $logger;
	private $logLevel;

	public function __construct( TemplateBasedMailer $mailer, LoggerInterface $logger ) {
		$this->mailer = $mailer;
		$this->logger = $logger;
		$this->logLevel = LogLevel::CRITICAL;
	}

	/**
	 * @throws \RuntimeException
	 */
	public function sendMail( EmailAddress $recipient, array $templateArguments = [] ) {
		try {
			$this->mailer->sendMail( $recipient, $templateArguments );
		}
		catch ( \RuntimeException $ex ) {
			$this->logger->log( $this->logLevel, $ex->getMessage(), [ self::CONTEXT_EXCEPTION_KEY => $ex ] );
			throw $ex;
		}
	}

}
