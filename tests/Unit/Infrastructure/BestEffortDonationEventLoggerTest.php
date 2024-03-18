<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\Unit\Infrastructure;

use PHPUnit\Framework\MockObject\MockObject;
use Psr\Log\LoggerInterface;
use WMDE\Fundraising\DonationContext\Infrastructure\BestEffortDonationEventLogger;
use WMDE\Fundraising\DonationContext\Infrastructure\DonationEventLogException;
use WMDE\Fundraising\DonationContext\Infrastructure\DonationEventLogger;
use WMDE\Fundraising\DonationContext\Tests\Fixtures\DonationEventLoggerSpy;

/**
 * @covers \WMDE\Fundraising\DonationContext\Infrastructure\BestEffortDonationEventLogger
 */
class BestEffortDonationEventLoggerTest extends \PHPUnit\Framework\TestCase {

	private const DONATION_ID = 1337;
	private const MESSAGE = 'a semi-important event has occured';

	public function testLogDataIsPassed(): void {
		$eventLogger = new DonationEventLoggerSpy();
		$bestEffortLogger = new BestEffortDonationEventLogger(
			$eventLogger,
			$this->getLogger()
		);
		$bestEffortLogger->log( self::DONATION_ID, self::MESSAGE );
		$this->assertCount( 1, $eventLogger->getLogCalls() );
	}

	public function testWhenNoExceptionOccurs_nothingIsLogged(): void {
		$eventLogger = new DonationEventLoggerSpy();
		$logger = $this->getLogger();
		$logger->expects( $this->never() )->method( $this->anything() );
		$bestEffortLogger = new BestEffortDonationEventLogger(
			$eventLogger,
			$logger
		);
		$bestEffortLogger->log( self::DONATION_ID, self::MESSAGE );
	}

	public function testWhenEventLoggerThrows_itIsLogged(): void {
		/** @var DonationEventLogger&MockObject $eventLogger */
		$eventLogger = $this->createMock( DonationEventLogger::class );
		$eventLogger->method( 'log' )->willThrowException( new DonationEventLogException( 'Fire Alarm!' ) );
		$logger = $this->getLogger();
		$logger->expects( $this->once() )->method( 'error' );
		$bestEffortLogger = new BestEffortDonationEventLogger(
			$eventLogger,
			$logger
		);
		$bestEffortLogger->log( self::DONATION_ID, self::MESSAGE );
	}

	/**
	 * @return LoggerInterface&MockObject
	 */
	private function getLogger(): LoggerInterface {
		return $this->createMock( LoggerInterface::class );
	}
}
