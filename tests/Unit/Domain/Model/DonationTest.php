<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\Unit\Domain\Model;

use RuntimeException;
use WMDE\Fundraising\Frontend\Domain\Model\DirectDebitPayment;
use WMDE\Fundraising\Frontend\Domain\Model\Donation;
use WMDE\Fundraising\Frontend\Domain\Model\DonationPayment;
use WMDE\Fundraising\Frontend\Domain\Model\Euro;
use WMDE\Fundraising\Frontend\Domain\Model\PaymentType;
use WMDE\Fundraising\Frontend\Tests\Data\ValidDonation;

/**
 * @covers WMDE\Fundraising\Frontend\Domain\Model\Donation
 *
 * @license GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class DonationTest extends \PHPUnit_Framework_TestCase {

	public function testGivenNonDirectDebitDonation_cancellationFails() {
		$donation = ValidDonation::newBankTransferDonation();

		$this->expectException( RuntimeException::class );
		$donation->cancel();
	}

	public function testGivenDirectDebitDonation_cancellationSucceeds() {
		$donation = ValidDonation::newDirectDebitDonation();

		$donation->cancel();
		$this->assertSame( Donation::STATUS_CANCELLED, $donation->getStatus() );
	}

	/**
	 * @dataProvider nonCancellableStatusProvider
	 */
	public function testGivenNonNewStatus_cancellationFails( $nonCancellableStatus ) {
		$donation = $this->newDirectDebitDonationWithStatus( $nonCancellableStatus );

		$this->expectException( RuntimeException::class );
		$donation->cancel();
	}

	private function newDirectDebitDonationWithStatus( string $status ) {
		return new Donation(
			null,
			$status,
			ValidDonation::newDonor(),
			new DonationPayment(
				Euro::newFromFloat( 13.37 ),
				3,
				new DirectDebitPayment( ValidDonation::newBankData() )
			),
			Donation::OPTS_INTO_NEWSLETTER,
			ValidDonation::newTrackingInfo()
		);
	}

	public function nonCancellableStatusProvider() {
		return [
			[ Donation::STATUS_CANCELLED ],
			[ Donation::STATUS_EXTERNAL_BOOKED ],
			[ Donation::STATUS_EXTERNAL_INCOMPLETE ],
			[ Donation::STATUS_PROMISE ],
		];
	}

	public function testGivenNewStatus_cancellationSucceeds() {
		$donation = ValidDonation::newDirectDebitDonation();

		$donation->cancel();
		$this->assertSame( Donation::STATUS_CANCELLED, $donation->getStatus() );
	}

	public function testGivenModerationStatus_cancellationSucceeds() {
		$donation = ValidDonation::newDirectDebitDonation();

		$donation->markForModeration();
		$donation->cancel();
		$this->assertSame( Donation::STATUS_CANCELLED, $donation->getStatus() );
	}

	public function testIdIsNullWhenNotAssigned() {
		$this->assertNull( ValidDonation::newDirectDebitDonation()->getId() );
	}

	public function testCanAssignIdToNewDonation() {
		$donation = ValidDonation::newDirectDebitDonation();

		$donation->assignId( 42 );
		$this->assertSame( 42, $donation->getId() );
	}

	public function testCannotAssignIdToDonationWithIdentity() {
		$donation = ValidDonation::newDirectDebitDonation();
		$donation->assignId( 42 );

		$this->expectException( RuntimeException::class );
		$donation->assignId( 43 );
	}

}