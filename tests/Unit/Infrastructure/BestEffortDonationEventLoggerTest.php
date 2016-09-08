<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\Unit\Infrastructure;

use Psr\Log\LoggerInterface;
use WMDE\Fundraising\Frontend\DonatingContext\Infrastructure\BestEffortDonationEventLogger;
use WMDE\Fundraising\Frontend\DonatingContext\Infrastructure\DonationEventLogException;
use WMDE\Fundraising\Frontend\DonatingContext\Infrastructure\DonationEventLogger;
use WMDE\Fundraising\Frontend\DonatingContext\Tests\Fixtures\DonationEventLoggerSpy;

/**
 * @covers WMDE\Fundraising\Frontend\DonatingContext\Infrastructure\BestEffortDonationEventLogger
 *
 * @licence GNU GPL v2+
 * @author Gabriel Birke < gabriel.birke@wikimedia.de >
 */
class BestEffortDonationEventLoggerTest extends \PHPUnit_Framework_TestCase {

	const DONATION_ID = 1337;
	const MESSAGE = 'a semi-important event has occured';

	public function testLogDataIsPassed() {
		$eventLogger = new DonationEventLoggerSpy();
		$bestEffortLogger = new BestEffortDonationEventLogger(
			$eventLogger,
			$this->getLogger()
		);
		$bestEffortLogger->log( self::DONATION_ID, self::MESSAGE );
		$this->assertCount( 1, $eventLogger->getLogCalls() );
	}

	public function testWhenNoExceptionOccurs_nothingIsLogged() {
		$eventLogger = new DonationEventLoggerSpy();
		$logger = $this->getLogger();
		$logger->expects( $this->never() )->method( $this->anything() );
		$bestEffortLogger = new BestEffortDonationEventLogger(
			$eventLogger,
			$logger
		);
		$bestEffortLogger->log( self::DONATION_ID, self::MESSAGE );
	}

	public function testWhenEventLoggerThrows_itIsLogged() {
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
