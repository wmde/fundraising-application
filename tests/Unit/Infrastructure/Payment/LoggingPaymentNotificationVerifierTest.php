<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\Unit\Infrastructure\Payment;

use PHPUnit\Framework\MockObject\MockObject;
use Psr\Log\LogLevel;
use WMDE\Fundraising\Frontend\Infrastructure\Payment\LoggingPaymentNotificationVerifier;
use WMDE\Fundraising\Frontend\Infrastructure\Payment\PayPalPaymentNotificationVerifier;
use WMDE\Fundraising\Frontend\Infrastructure\Payment\PayPalPaymentNotificationVerifierException;
use WMDE\PsrLogTestDoubles\LoggerSpy;

/**
 * @covers \WMDE\Fundraising\Frontend\Infrastructure\Payment\LoggingPaymentNotificationVerifier
 *
 * @license GPL-2.0-or-later
 * @author Kai Nissen < kai.nissen@wikimedia.de >
 */
class LoggingPaymentNotificationVerifierTest extends \PHPUnit\Framework\TestCase {

	public function testWhenVerifierThrowsException_loggingVerifierPassesItOn(): void {
		$loggingVerifier = new LoggingPaymentNotificationVerifier(
			$this->newThrowingVerifier(),
			new LoggerSpy()
		);

		$this->expectException( PayPalPaymentNotificationVerifierException::class );
		$loggingVerifier->verify( [] );
	}

	/**
	 * @param PayPalPaymentNotificationVerifierException|null $exception
	 *
	 * @return PayPalPaymentNotificationVerifier&MockObject
	 */
	private function newThrowingVerifier( PayPalPaymentNotificationVerifierException $exception = null ): PayPalPaymentNotificationVerifier {
		$verifier = $this->createMock( PayPalPaymentNotificationVerifier::class );

		if ( $exception === null ) {
			$exception = new PayPalPaymentNotificationVerifierException( 'reticulation of splines failed' );
		}

		$verifier->expects( $this->any() )
			->method( 'verify' )
			->willThrowException( $exception );

		return $verifier;
	}

	public function testWhenVerifierThrowsException_itIsLogged(): void {
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

	private function assertExceptionLoggedAsCritical( string $expectedExceptionType, LoggerSpy $logger ): void {
		$this->assertNotEmpty( $logger->getLogCalls(), 'There should be at least one log call' );

		$logCall = $logger->getLogCalls()->getFirstCall();

		$this->assertSame( LogLevel::CRITICAL, $logCall->getLevel() );
		$this->assertArrayHasKey( 'exception', $logCall->getContext(), 'the log context should contain an exception element' );
		$this->assertInstanceOf( $expectedExceptionType, $logCall->getContext()['exception'] );
	}

	public function testGivenAnExceptionForUnsupportedPaymentMethod_itIsLoggedAsInfo(): void {
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

	private function assertUnsupportedMethodWasLogged( string $expectedMethodName, LoggerSpy $logger ): void {
		$this->assertNotEmpty( $logger->getLogCalls(), 'There should be at least one log call' );

		$logCall = $logger->getLogCalls()->getFirstCall();

		$this->assertSame( LogLevel::INFO, $logCall->getLevel() );
		$this->assertArrayHasKey( 'payment_status', $logCall->getContext(), 'the log context should contain a payment_status element' );
		$this->assertSame( $expectedMethodName, $logCall->getContext()['payment_status'] );
	}

	public function testWhenVerifierThrowsException_requestIsLoggedAsDebugInfo(): void {
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

	private function assertRequestLoggedAsDebugInfo( LoggerSpy $logger ): void {
		$this->assertGreaterThan( 1, count( $logger->getLogCalls() ), 'There should be at least two log calls' );

		$logCall = $logger->getLogCalls()->getIterator()[1];

		$this->assertSame( LogLevel::DEBUG, $logCall->getLevel() );
		$this->assertSame(
			[ 'item_name'  => 'Welcome to Wikipedia' ],
			$logCall->getContext(),
			'the third log argument should contain the request'
		);
	}

	public function testWhenVerifierSucceeds_nothingIsLogged(): void {
		$logger = new LoggerSpy();
		$verifierMock = $this->getMockBuilder( PayPalPaymentNotificationVerifier::class )->disableOriginalConstructor()->getMock();
		$verifier = new LoggingPaymentNotificationVerifier(
			$verifierMock,
			$logger
		);
		$verifier->verify( [] );

		$this->assertcount( 0, $logger->getLogCalls(), 'No log calls should be made' );
	}

}
