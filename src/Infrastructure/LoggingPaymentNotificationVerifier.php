<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Infrastructure;

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

	public function verify( array $request ): void {
		try {
			$this->verifier->verify( $request );
		} catch ( PayPalPaymentNotificationVerifierException $exception ) {
			if ( $exception->getCode() == PayPalPaymentNotificationVerifierException::ERROR_UNSUPPORTED_STATUS ) {
				$this->logger->log( LogLevel::INFO, 'Unsupported payment_status', $request );
				throw $exception;
			}

			$this->logger->log( $this->logLevel, $exception->getMessage(), [ self::CONTEXT_EXCEPTION_KEY => $exception ] );
			$this->logger->log( LogLevel::DEBUG, 'Paypal request data', $request );
			throw $exception;
		}
	}

}
