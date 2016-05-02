<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\Unit\Infrastructure\Repositories;

use Psr\Log\LogLevel;
use WMDE\Fundraising\Frontend\Domain\Repositories\DonationRepository;
use WMDE\Fundraising\Frontend\Domain\Repositories\GetDonationException;
use WMDE\Fundraising\Frontend\Domain\Repositories\StoreDonationException;
use WMDE\Fundraising\Frontend\Infrastructure\Repositories\LoggingDonationRepository;
use WMDE\Fundraising\Frontend\Tests\Data\ValidDonation;
use WMDE\Fundraising\Frontend\Tests\Fixtures\LoggerSpy;

/**
 * @covers WMDE\Fundraising\Frontend\Infrastructure\Repositories\LoggingDonationRepository
 *
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class LoggingDonationRepositoryTest extends \PHPUnit_Framework_TestCase {

	public function testWhenGetDonationByIdThrowException_itIsLogged() {
		$loggingRepo = new LoggingDonationRepository(
			$this->newThrowingRepository(),
			new LoggerSpy()
		);

		$this->expectException( GetDonationException::class );
		$loggingRepo->getDonationById( 1337 );
	}

	private function newThrowingRepository(): DonationRepository {
		$repository = $this->getMock( DonationRepository::class );

		$repository->expects( $this->any() )
			->method( 'getDonationById' )
			->willThrowException( new GetDonationException() );

		$repository->expects( $this->any() )
			->method( 'storeDonation' )
			->willThrowException( new StoreDonationException() );

		return $repository;
	}

	public function testWhenGetDonationByIdThrowException_itIsNotFullyCaught() {
		$logger = new LoggerSpy();

		$loggingRepo = new LoggingDonationRepository(
			$this->newThrowingRepository(),
			$logger
		);

		try {
			$loggingRepo->getDonationById( 1337 );
		}
		catch ( GetDonationException $ex ) {
		}

		$this->assertExceptionLoggedAsCritical( GetDonationException::class, $logger );
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

	public function testWhenStoreDonationThrowException_itIsLogged() {
		$loggingRepo = new LoggingDonationRepository(
			$this->newThrowingRepository(),
			new LoggerSpy()
		);

		$this->expectException( StoreDonationException::class );
		$loggingRepo->storeDonation( ValidDonation::newDirectDebitDonation() );
	}

	public function testWhenStoreDonationThrowException_itIsNotFullyCaught() {
		$logger = new LoggerSpy();

		$loggingRepo = new LoggingDonationRepository(
			$this->newThrowingRepository(),
			$logger
		);

		try {
			$loggingRepo->storeDonation( ValidDonation::newDirectDebitDonation() );
		}
		catch ( StoreDonationException $ex ) {
		}

		$this->assertExceptionLoggedAsCritical( StoreDonationException::class, $logger );
	}

}
