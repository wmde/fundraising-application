<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\DonationContext\Tests\Unit\Domain\Model;

use RuntimeException;
use WMDE\Fundraising\Frontend\DonationContext\Domain\Model\Donation;
use WMDE\Fundraising\Frontend\DonationContext\Tests\Data\ValidDonation;
use WMDE\Fundraising\Frontend\PaymentContext\Domain\Model\PayPalData;

/**
 * @covers WMDE\Fundraising\Frontend\DonationContext\Domain\Model\Donation
 *
 * @license GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class DonationTest extends \PHPUnit\Framework\TestCase {

	public function testGivenNonDirectDebitDonation_cancellationFails(): void {
		$donation = ValidDonation::newBankTransferDonation();

		$this->expectException( RuntimeException::class );
		$donation->cancel();
	}

	public function testGivenDirectDebitDonation_cancellationSucceeds(): void {
		$donation = ValidDonation::newDirectDebitDonation();

		$donation->cancel();
		$this->assertSame( Donation::STATUS_CANCELLED, $donation->getStatus() );
	}

	/**
	 * @dataProvider nonCancellableStatusProvider
	 */
	public function testGivenNonNewStatus_cancellationFails( $nonCancellableStatus ): void {
		$donation = $this->newDirectDebitDonationWithStatus( $nonCancellableStatus );

		$this->expectException( RuntimeException::class );
		$donation->cancel();
	}

	private function newDirectDebitDonationWithStatus( string $status ) {
		return new Donation(
			null,
			$status,
			ValidDonation::newDonor(),
			ValidDonation::newDirectDebitPayment(),
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

	public function testGivenNewStatus_cancellationSucceeds(): void {
		$donation = ValidDonation::newDirectDebitDonation();

		$donation->cancel();
		$this->assertSame( Donation::STATUS_CANCELLED, $donation->getStatus() );
	}

	public function testModerationStatusCanBeQueried(): void {
		$donation = ValidDonation::newDirectDebitDonation();

		$donation->markForModeration();
		$this->assertTrue( $donation->needsModeration() );
	}

	public function testGivenModerationStatus_cancellationSucceeds(): void {
		$donation = ValidDonation::newDirectDebitDonation();

		$donation->markForModeration();
		$donation->cancel();
		$this->assertSame( Donation::STATUS_CANCELLED, $donation->getStatus() );
	}

	public function testIdIsNullWhenNotAssigned(): void {
		$this->assertNull( ValidDonation::newDirectDebitDonation()->getId() );
	}

	public function testCanAssignIdToNewDonation(): void {
		$donation = ValidDonation::newDirectDebitDonation();

		$donation->assignId( 42 );
		$this->assertSame( 42, $donation->getId() );
	}

	public function testCannotAssignIdToDonationWithIdentity(): void {
		$donation = ValidDonation::newDirectDebitDonation();
		$donation->assignId( 42 );

		$this->expectException( RuntimeException::class );
		$donation->assignId( 43 );
	}

	public function testGivenNonExternalPaymentType_confirmBookedThrowsException(): void {
		$donation = ValidDonation::newDirectDebitDonation();

		$this->expectException( RuntimeException::class );
		$this->expectExceptionMessageRegExp( '/Only external payments/' );
		$donation->confirmBooked();
	}

	/**
	 * @dataProvider statusesThatDoNotAllowForBookingProvider
	 */
	public function testGivenStatusThatDoesNotAllowForBooking_confirmBookedThrowsException( Donation $donation ): void {
		$this->expectException( RuntimeException::class );
		$donation->confirmBooked();
	}

	public function statusesThatDoNotAllowForBookingProvider() {
		return [
			[ ValidDonation::newBookedPayPalDonation() ],
			[ ValidDonation::newBookedCreditCardDonation() ],
		];
	}

	/**
	 * @dataProvider statusesThatAllowsForBookingProvider
	 */
	public function testGivenStatusThatAllowsForBooking_confirmBookedSetsBookedStatus( Donation $donation ): void {
		$donation->confirmBooked();
		$this->assertSame( Donation::STATUS_EXTERNAL_BOOKED, $donation->getStatus() );
	}

	public function statusesThatAllowsForBookingProvider() {
		return [
			[ ValidDonation::newIncompletePayPalDonation() ],
			[ ValidDonation::newIncompleteCreditCardDonation() ],
			[ $this->newInModerationPayPalDonation() ],
			[ ValidDonation::newCancelledPayPalDonation() ],
		];
	}

	private function newInModerationPayPalDonation(): Donation {
		$donation = ValidDonation::newIncompletePayPalDonation();
		$donation->markForModeration();
		return $donation;
	}

	public function testAddCommentThrowsExceptionWhenCommentAlreadySet(): void {
		$donation = new Donation(
			null,
			Donation::STATUS_NEW,
			ValidDonation::newDonor(),
			ValidDonation::newDirectDebitPayment(),
			Donation::OPTS_INTO_NEWSLETTER,
			ValidDonation::newTrackingInfo(),
			ValidDonation::newPublicComment()
		);

		$this->expectException( RuntimeException::class );
		$donation->addComment( ValidDonation::newPublicComment() );
	}

	public function testAddCommentSetsWhenCommentNotSetYet(): void {
		$donation = new Donation(
			null,
			Donation::STATUS_NEW,
			ValidDonation::newDonor(),
			ValidDonation::newDirectDebitPayment(),
			Donation::OPTS_INTO_NEWSLETTER,
			ValidDonation::newTrackingInfo(),
			null
		);

		$donation->addComment( ValidDonation::newPublicComment() );
		$this->assertEquals( ValidDonation::newPublicComment(), $donation->getComment() );
	}

	public function testWhenNoCommentHasBeenSet_getCommentReturnsNull(): void {
		$this->assertNull( ValidDonation::newDirectDebitDonation()->getComment() );
	}

	public function testWhenCompletingBookingOfExternalPaymentInModeration_commentIsMadePrivate(): void {
		$donation = $this->newInModerationPayPalDonation();
		$donation->addComment( ValidDonation::newPublicComment() );

		$donation->confirmBooked();

		$this->assertFalse( $donation->getComment()->isPublic() );
	}

	public function testWhenCompletingBookingOfCancelledExternalPayment_commentIsMadePrivate(): void {
		$donation = ValidDonation::newCancelledPayPalDonation();
		$donation->addComment( ValidDonation::newPublicComment() );

		$donation->confirmBooked();

		$this->assertFalse( $donation->getComment()->isPublic() );
	}

	public function testWhenCompletingBookingOfCancelledExternalPayment_lackOfCommentCausesNoError(): void {
		$donation = ValidDonation::newCancelledPayPalDonation();

		$donation->confirmBooked();

		$this->assertFalse( $donation->hasComment() );
	}

	public function testWhenConstructingWithInvalidStatus_exceptionIsThrown(): void {
		$this->expectException( \InvalidArgumentException::class );

		new Donation(
			null,
			'Such invalid status',
			ValidDonation::newDonor(),
			ValidDonation::newDirectDebitPayment(),
			Donation::OPTS_INTO_NEWSLETTER,
			ValidDonation::newTrackingInfo(),
			null
		);
	}

	public function testWhenNonExternalPaymentIsNotifiedOfPolicyValidationFailure_itIsPutInModeration(): void {
		$donation = ValidDonation::newBankTransferDonation();
		$donation->notifyOfPolicyValidationFailure();
		$this->assertTrue( $donation->needsModeration() );
	}

	public function testWhenExternalPaymentIsNotifiedOfPolicyValidationFailure_itIsNotPutInModeration(): void {
		$donation = ValidDonation::newIncompletePayPalDonation();
		$donation->notifyOfPolicyValidationFailure();
		$this->assertFalse( $donation->needsModeration() );
	}

}
