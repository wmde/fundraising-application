<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\Unit\Infrastructure;

use Psr\Log\LogLevel;
use WMDE\Fundraising\Frontend\Infrastructure\LoggingPaymentNotificationVerifier;
use WMDE\Fundraising\Frontend\Infrastructure\PayPalPaymentNotificationVerifier;
use WMDE\Fundraising\Frontend\Infrastructure\PayPalPaymentNotificationVerifierException;
use WMDE\Fundraising\Frontend\Tests\Fixtures\LoggerSpy;

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
		$logCalls = $logger->getLogCalls();

		$this->assertNotEmpty( $logCalls, 'There should be at least one log call' );
		$logCall = $logCalls[0];

		$this->assertSame( LogLevel::CRITICAL, $logCall[0] );
		$this->assertInternalType( 'array', $logCall[2], 'the third log argument should be an array' );
		$this->assertArrayHasKey( 'exception', $logCall[2], 'the log context should contain an exception element' );
		$this->assertInstanceOf( $expectedExceptionType, $logCall[2]['exception'] );
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
		$logCalls = $logger->getLogCalls();

		$this->assertCount( 1, $logCalls, 'There should only be one log call' );
		$logCall = $logCalls[0];

		$this->assertSame( LogLevel::INFO, $logCall[0] );
		$this->assertArrayHasKey( 'payment_status', $logCall[2], 'the log context should contain a payment_status element' );
		$this->assertSame( $expectedMethodName, $logCall[2]['payment_status'] );
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
		$logCalls = $logger->getLogCalls();

		$this->assertGreaterThan( 1, count( $logCalls ), 'There should be at least two log calls' );
		$logCall = $logCalls[1];

		$this->assertSame( LogLevel::DEBUG, $logCall[0] );
		$this->assertEquals( [ 'item_name'  => 'Welcome to Wikipedia' ], $logCall[2], 'the third log argument should contain the request' );
	}

	public function testWhenVerifierSucceeds_nothingIsLogged() {
		$logger = new LoggerSpy();
		$verifierMock = $this->getMockBuilder( PayPalPaymentNotificationVerifier::class )->disableOriginalConstructor()->getMock();
		$verifier = new LoggingPaymentNotificationVerifier(
			$verifierMock,
			$logger
		);
		$verifier->verify( [] );

		$logger->assertNoCalls();
	}

}
