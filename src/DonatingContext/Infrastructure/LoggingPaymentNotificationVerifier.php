<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\DonatingContext\Infrastructure;

use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;

/**
 * @license GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 * @author Kai Nissen < kai.nissen@wikimedia.de >
 */
class LoggingPaymentNotificationVerifier implements PaymentNotificationVerifier {

	const CONTEXT_EXCEPTION_KEY = 'exception';

	private $verifier;
	private $logger;
	private $logLevel;

	public function __construct( PaymentNotificationVerifier $verifier, LoggerInterface $logger ) {
		$this->verifier = $verifier;
		$this->logger = $logger;
		$this->logLevel = LogLevel::CRITICAL;
	}

	public function verify( array $request ) {
		try {
			$this->verifier->verify( $request );
		} catch ( PayPalPaymentNotificationVerifierException $exception ) {
			$this->logger->log( $this->logLevel, $exception->getMessage(), [ self::CONTEXT_EXCEPTION_KEY => $exception ] );
			throw $exception;
		}
	}

}
