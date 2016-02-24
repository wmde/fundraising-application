<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\Integration\DataAccess;

use Doctrine\ORM\EntityManager;
use WMDE\Fundraising\Frontend\DataAccess\DoctrineDonationRepository;
use WMDE\Fundraising\Frontend\DataAccess\UniqueTransferCodeGenerator;
use WMDE\Fundraising\Frontend\Domain\TransferCodeGenerator;
use WMDE\Fundraising\Frontend\Tests\Data\ValidDonation;
use WMDE\Fundraising\Frontend\Tests\TestEnvironment;

/**
 * @covers WMDE\Fundraising\Frontend\DataAccess\UniqueTransferCodeGenerator
 *
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class UniqueTransferCodeGeneratorTest extends \PHPUnit_Framework_TestCase {

	/**
	 * @var EntityManager
	 */
	private $entityManager;

	public function setUp() {
		$this->entityManager = TestEnvironment::newInstance()->getFactory()->getEntityManager();
	}

	private function newUniqueGenerator(): TransferCodeGenerator {
		return new UniqueTransferCodeGenerator(
			$this->newFakeGenerator(),
			$this->entityManager
		);
	}

	private function newFakeGenerator(): TransferCodeGenerator {
		return new class() implements TransferCodeGenerator {
			private $position = 0;

			public function generateTransferCode(): string {
				return ['first', 'second', 'third'][$this->position++];
			}
		};
	}

	public function testWhenFirstResultIsUnique_itGetsReturned() {
		$this->assertSame( 'first', $this->newUniqueGenerator()->generateTransferCode() );
	}

	public function testWhenFirstResultIsNotUnique_secondResultGetsReturned() {
		$this->storeDonationWithTransferCode( 'first' );
		$this->assertSame( 'second', $this->newUniqueGenerator()->generateTransferCode() );
	}

	private function storeDonationWithTransferCode( string $code ) {
		$donation = ValidDonation::newBankTransferDonation( $code );
		( new DoctrineDonationRepository( $this->entityManager ) )->storeDonation( $donation );
	}

	public function testWhenFirstAndSecondResultsAreNotUnique_thirdResultGetsReturned() {
		$this->storeDonationWithTransferCode( 'first' );
		$this->storeDonationWithTransferCode( 'second' );
		$this->assertSame( 'third', $this->newUniqueGenerator()->generateTransferCode() );
	}

}