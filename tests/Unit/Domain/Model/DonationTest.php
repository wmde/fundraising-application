<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\Unit\Domain\Model;

use RuntimeException;
use WMDE\Fundraising\Frontend\Domain\Model\Donation;
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
		$this->assertSame( Donation::STATUS_DELETED, $donation->getStatus() );
	}

	/**
	 * @dataProvider nonCancellableStatusProvider
	 */
	public function testGivenNonNewStatus_cancellationFails( $nonCancellableStatus ) {
		$donation = ValidDonation::newDirectDebitDonation();
		$donation->setStatusForTest( $nonCancellableStatus );

		$this->expectException( RuntimeException::class );
		$donation->cancel();
	}

	public function nonCancellableStatusProvider() {
		return [
			[ Donation::STATUS_DELETED ],
			[ Donation::STATUS_EXTERNAL_BOOKED ],
			[ Donation::STATUS_EXTERNAL_INCOMPLETE ],
			[ Donation::STATUS_PROMISE ],
		];
	}

	public function testGivenNewStatus_cancellationSucceeds() {
		$donation = ValidDonation::newDirectDebitDonation();

		$donation->cancel();
		$this->assertSame( Donation::STATUS_DELETED, $donation->getStatus() );
	}

	public function testGivenModerationStatus_cancellationSucceeds() {
		$donation = ValidDonation::newDirectDebitDonation();

		$donation->markForModeration();
		$donation->cancel();
		$this->assertSame( Donation::STATUS_DELETED, $donation->getStatus() );
	}

	public function testIdIsNullWhenNotAssigned() {
		$this->assertNull( ValidDonation::newDirectDebitDonation()->getId() );
	}

	public function testCanAssignIdToNewDonation() {
		$donation = ValidDonation::newDirectDebitDonation();

		$donation->setId( 42 );
		$this->assertSame( 42, $donation->getId() );
	}

	public function testCannotAssignIdToDonationWithIdentity() {
		$donation = ValidDonation::newDirectDebitDonation();
		$donation->setId( 42 );

		$this->expectException( RuntimeException::class );
		$donation->setId( 43 );
	}

}