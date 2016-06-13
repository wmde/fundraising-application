<?php


namespace WMDE\Fundraising\Frontend\Tests\Integration\DataAccess;

use Doctrine\ORM\EntityManager;
use WMDE\Fundraising\Entities\Donation;
use WMDE\Fundraising\Frontend\DataAccess\DoctrineDonationEventLogger;
use WMDE\Fundraising\Frontend\Infrastructure\DonationEventLogException;
use WMDE\Fundraising\Frontend\Tests\Fixtures\ThrowingEntityManager;
use WMDE\Fundraising\Frontend\Tests\TestEnvironment;

class DoctrineDonationEventLoggerTest extends \PHPUnit_Framework_TestCase {

	const DEFAULT_MESSAGE = 'Log message';
	const LOG_TIMESTAMP = '2015-10-21 21:00:04';

	/**
	 * @var EntityManager
	 */
	private $entityManager;

	public function setUp() {
		$this->entityManager = TestEnvironment::newInstance()->getFactory()->getEntityManager();
	}

	public function testIfDonationDoesNotExistLoggingFails() {
		$logger = new DoctrineDonationEventLogger( $this->entityManager, $this->getDefaultTimeFunction() );

		$this->expectException( DonationEventLogException::class );
		$logger->log( 1234, self::DEFAULT_MESSAGE );
	}

	public function testWhenPersistenceFails_domainExceptionIsThrown() {
		$logger = new DoctrineDonationEventLogger(
			ThrowingEntityManager::newInstance( $this ),
			$this->getDefaultTimeFunction()
		);

		$this->expectException( DonationEventLogException::class );
		$logger->log( 1234, self::DEFAULT_MESSAGE );
	}

	public function testWhenNoLogExists_logGetsAdded() {
		$donation = new Donation();
		$this->entityManager->persist( $donation );
		$this->entityManager->flush();
		$donationId = $donation->getId();

		$logger = new DoctrineDonationEventLogger( $this->entityManager, $this->getDefaultTimeFunction() );

		$logger->log( $donationId, self::DEFAULT_MESSAGE );

		$donation = $this->getDonationById( $donationId );
		$data = $donation->getDecodedData();

		$expectedLog = [
			self::LOG_TIMESTAMP => self::DEFAULT_MESSAGE
		];
		$this->assertArrayHasKey( 'log', $data );
		$this->assertEquals( $expectedLog, $data['log'] );
	}

	public function testWhenLogExists_logGetsAppended() {
		$donation = new Donation();
		$donation->encodeAndSetData( [ 'log' => [ '2014-01-01 0:00:00' => 'New year!' ] ] );
		$this->entityManager->persist( $donation );
		$this->entityManager->flush();
		$donationId = $donation->getId();

		$logger = new DoctrineDonationEventLogger( $this->entityManager, $this->getDefaultTimeFunction() );

		$logger->log( $donationId, self::DEFAULT_MESSAGE );

		$donation = $this->getDonationById( $donationId );
		$data = $donation->getDecodedData();

		$expectedLog = [
			'2014-01-01 0:00:00' => 'New year!',
			self::LOG_TIMESTAMP => self::DEFAULT_MESSAGE
		];
		$this->assertArrayHasKey( 'log', $data );
		$this->assertEquals( $expectedLog, $data['log'] );
	}

	private function getDonationById( int $donationId ): Donation {
		return $this->entityManager->find( Donation::class, $donationId );
	}

	// always return fixed date
	private function getDefaultTimeFunction() {
		return function() {
			return self::LOG_TIMESTAMP;
		};
	}

}
