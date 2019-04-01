<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\Unit\Infrastructure;

use Psr\Log\LoggerInterface;
use WMDE\Fundraising\DonationContext\Infrastructure\BestEffortDonationEventLogger;
use WMDE\Fundraising\DonationContext\Infrastructure\DonationEventLogException;
use WMDE\Fundraising\DonationContext\Infrastructure\DonationEventLogger;
use WMDE\Fundraising\DonationContext\Tests\Fixtures\DonationEventLoggerSpy;

/**
 * @covers \WMDE\Fundraising\DonationContext\Infrastructure\BestEffortDonationEventLogger
 *
 * @licence GNU GPL v2+
 * @author Gabriel Birke < gabriel.birke@wikimedia.de >
 */
class BestEffortDonationEventLoggerTest extends \PHPUnit\Framework\TestCase {

	const DONATION_ID = 1337;
	const MESSAGE = 'a semi-important event has occured';

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
		$eventLogger = $this->createMock( DonationEventLogger::class );
		$eventLogger->method( 'log' )->will( $this->throwException( new DonationEventLogException( 'Fire Alarm!' ) ) );
		$logger = $this->getLogger();
		$logger->expects( $this->once() )->method( 'error' );
		$bestEffortLogger = new BestEffortDonationEventLogger(
			$eventLogger,
			$logger
		);
		$bestEffortLogger->log( self::DONATION_ID, self::MESSAGE );
	}

	/**
	 * @return LoggerInterface|\PHPUnit_Framework_MockObject_MockObject
	 */
	private function getLogger(): LoggerInterface {
		return $this->createMock( LoggerInterface::class );
	}
}
