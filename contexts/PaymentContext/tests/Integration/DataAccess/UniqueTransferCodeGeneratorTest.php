<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\Integration\PaymentContext\DataAccess;

use Doctrine\ORM\EntityManager;
use WMDE\Euro\Euro;
use WMDE\Fundraising\Frontend\DonationContext\DataAccess\DoctrineDonationRepository;
use WMDE\Fundraising\Frontend\DonationContext\Domain\Model\Donation;
use WMDE\Fundraising\Frontend\DonationContext\Domain\Model\DonationPayment;
use WMDE\Fundraising\Frontend\PaymentContext\DataAccess\UniqueTransferCodeGenerator;
use WMDE\Fundraising\Frontend\PaymentContext\Domain\Model\BankTransferPayment;
use WMDE\Fundraising\Frontend\PaymentContext\Domain\TransferCodeGenerator;
use WMDE\Fundraising\Frontend\DonationContext\Tests\Data\ValidDonation;
use WMDE\Fundraising\Frontend\Tests\TestEnvironment;

/**
 * @covers WMDE\Fundraising\Frontend\PaymentContext\DataAccess\UniqueTransferCodeGenerator
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
		$donation = new Donation(
			null,
			Donation::STATUS_NEW,
			ValidDonation::newDonor(),
			new DonationPayment(
				Euro::newFromFloat( 13.37 ),
				3,
				new BankTransferPayment( $code )
			),
			Donation::OPTS_INTO_NEWSLETTER,
			ValidDonation::newTrackingInfo()
		);

		( new DoctrineDonationRepository( $this->entityManager ) )->storeDonation( $donation );
	}

	public function testWhenFirstAndSecondResultsAreNotUnique_thirdResultGetsReturned() {
		$this->storeDonationWithTransferCode( 'first' );
		$this->storeDonationWithTransferCode( 'second' );
		$this->assertSame( 'third', $this->newUniqueGenerator()->generateTransferCode() );
	}

}