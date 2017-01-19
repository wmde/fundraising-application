<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\Unit\Infrastructure;

use Psr\Log\LogLevel;
use WMDE\Fundraising\Frontend\Infrastructure\LoggingPaymentNotificationVerifier;
use WMDE\Fundraising\Frontend\Infrastructure\PayPalPaymentNotificationVerifier;
use WMDE\Fundraising\Frontend\Infrastructure\PayPalPaymentNotificationVerifierException;
use WMDE\PsrLogTestDoubles\LoggerSpy;

/**
 * @covers WMDE\Fundraising\Frontend\Infrastructure\LoggingPaymentNotificationVerifier
 *
 * @licence GNU GPL v2+
 * @author Kai Nissen < kai.nissen@wikimedia.de >
 */
class LoggingPaymentNotificationVerifierTest extends \PHPUnit_Framework_TestCase {

	public function testWhenVerifierThrowsException_loggingVerifierPassesItOn() {
		$loggingVerifier = new LoggingPaymentNotificationVerifier(
			$this->newThrowingVerifier(),
			new LoggerSpy()
		);

		$this->expectException( PayPalPaymentNotificationVerifierException::class );
		$loggingVerifier->verify( [] );
	}

	private function newThrowingVerifier( PayPalPaymentNotificationVerifierException $exception = null ): PayPalPaymentNotificationVerifier {
		$verifier = $this->getMockBuilder( PayPalPaymentNotificationVerifier::class )->disableOriginalConstructor()->getMock();

		if ( is_null( $exception ) ) {
			$exception = new PayPalPaymentNotificationVerifierException( 'reticulation of splines failed' );
		}

		$verifier->expects( $this->any() )
			->method( 'verify' )
			->willThrowException( $exception );

		return $verifier;
	}

	public function testWhenVerifierThrowsException_itIsLogged() {
		$logger = new LoggerSpy();

		$loggingVerifier = new LoggingPaymentNotificationVerifier(
			$this->newThrowingVerifier(),
			$logger
		);

		try {
			$loggingVerifier->verify( [] );
		}
		catch ( PayPalPaymentNotificationVerifierException $ex ) {
		}

		$this->assertExceptionLoggedAsCritical( PayPalPaymentNotificationVerifierException::class, $logger );
	}

	private function assertExceptionLoggedAsCritical( string $expectedExceptionType, LoggerSpy $logger ) {
		$this->assertNotEmpty( $logger->getLogCalls(), 'There should be at least one log call' );

		$logCall = $logger->getLogCalls()->getFirstCall();

		$this->assertSame( LogLevel::CRITICAL, $logCall->getLevel() );
		$this->assertArrayHasKey( 'exception', $logCall->getContext(), 'the log context should contain an exception element' );
		$this->assertInstanceOf( $expectedExceptionType, $logCall->getContext()['exception'] );
	}

	public function testGivenAnExceptionForUnsupportedPaymentMethod_itIsLoggedAsInfo() {
		$logger = new LoggerSpy();

		$statusException = new PayPalPaymentNotificationVerifierException(
			'Unsupported status',
			PayPalPaymentNotificationVerifierException::ERROR_UNSUPPORTED_STATUS
		);
		$loggingVerifier = new LoggingPaymentNotificationVerifier(
			$this->newThrowingVerifier( $statusException ),
			$logger
		);

		try {
			$loggingVerifier->verify( [ 'payment_status' => 'Unknown' ] );
		}
		catch ( PayPalPaymentNotificationVerifierException $ex ) {
		}

		$this->assertUnsupportedMethodWasLogged( 'Unknown', $logger );
	}

	private function assertUnsupportedMethodWasLogged( string $expectedMethodName, LoggerSpy $logger ) {
		$this->assertNotEmpty( $logger->getLogCalls(), 'There should be at least one log call' );

		$logCall = $logger->getLogCalls()->getFirstCall();

		$this->assertSame( LogLevel::INFO, $logCall->getLevel() );
		$this->assertArrayHasKey( 'payment_status', $logCall->getContext(), 'the log context should contain a payment_status element' );
		$this->assertSame( $expectedMethodName, $logCall->getContext()['payment_status'] );
	}

	public function testWhenVerifierThrowsException_requestIsLoggedAsDebugInfo() {
		$logger = new LoggerSpy();

		$loggingVerifier = new LoggingPaymentNotificationVerifier(
			$this->newThrowingVerifier(),
			$logger
		);

		try {
			$loggingVerifier->verify( [ 'item_name'  => 'Welcome to Wikipedia' ] );
		}
		catch ( PayPalPaymentNotificationVerifierException $ex ) {
		}

		$this->assertRequestLoggedAsDebugInfo( $logger );
	}

	private function assertRequestLoggedAsDebugInfo( LoggerSpy $logger ) {
		$this->assertGreaterThan( 1, count( $logger->getLogCalls() ), 'There should be at least two log calls' );

		$logCall = $logger->getLogCalls()->getIterator()[1];

		$this->assertSame( LogLevel::DEBUG, $logCall->getLevel() );
		$this->assertSame(
			[ 'item_name'  => 'Welcome to Wikipedia' ],
			$logCall->getContext(),
			'the third log argument should contain the request'
		);
	}

	public function testWhenVerifierSucceeds_nothingIsLogged() {
		$logger = new LoggerSpy();
		$verifierMock = $this->getMockBuilder( PayPalPaymentNotificationVerifier::class )->disableOriginalConstructor()->getMock();
		$verifier = new LoggingPaymentNotificationVerifier(
			$verifierMock,
			$logger
		);
		$verifier->verify( [] );

		$this->assertEmpty( $logger->getLogCalls(), 'No log calls should be made' );
	}

}
