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

	private function newThrowingVerifier(): PayPalPaymentNotificationVerifier {
		$verifier = $this->getMockBuilder( PayPalPaymentNotificationVerifier::class )->disableOriginalConstructor()->getMock();

		$verifier->expects( $this->any() )
			->method( 'verify' )
			->willThrowException( new PayPalPaymentNotificationVerifierException( 'reticulation of splines failed' ) );

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

		$this->assertCount( 1, $logCalls, 'There should be exactly one log call' );
		$logCall = $logCalls[0];

		$this->assertSame( LogLevel::CRITICAL, $logCall[0] );
		$this->assertInternalType( 'array', $logCall[2], 'the third log argument should be an array' );
		$this->assertArrayHasKey( 'exception', $logCall[2], 'the log context should contain an exception element' );
		$this->assertInstanceOf( $expectedExceptionType, $logCall[2]['exception'] );
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
