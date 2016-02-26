<?php

namespace WMDE\Fundraising\Frontend\Tests\Unit\Domain\Model;

use RuntimeException;
use WMDE\Fundraising\Frontend\Domain\Model\Donation;
use WMDE\Fundraising\Frontend\Domain\Model\PaymentType;

/**
 * @covers WMDE\Fundraising\Frontend\Domain\Model\Donation
 *
 * @license GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class DonationTest extends \PHPUnit_Framework_TestCase {

	public function testGivenNonDirectDebitDonation_cancellationFails() {
		$donation = new Donation();
		$donation->setPaymentType( PaymentType::CREDIT_CARD );
		$donation->setStatus( Donation::STATUS_NEW );

		$this->expectException( RuntimeException::class );
		$donation->cancel();
	}

	public function testGivenDirectDebitDonation_cancellationSucceeds() {
		$donation = new Donation();
		$donation->setPaymentType( PaymentType::DIRECT_DEBIT );
		$donation->setStatus( Donation::STATUS_NEW );

		$donation->cancel();
		$this->assertSame( Donation::STATUS_DELETED, $donation->getStatus() );
	}

	/**
	 * @dataProvider nonCancellableStatusProvider
	 */
	public function testGivenNonNewStatus_cancellationFails( $nonCancellableStatus ) {
		$donation = new Donation();
		$donation->setPaymentType( PaymentType::DIRECT_DEBIT );
		$donation->setStatus( $nonCancellableStatus );

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

	/**
	 * @dataProvider cancellableStatusProvider
	 */
	public function testGivenNewStatus_cancellationSucceeds( $cancellableStatus ) {
		$donation = new Donation();
		$donation->setPaymentType( PaymentType::DIRECT_DEBIT );
		$donation->setStatus( $cancellableStatus );

		$donation->cancel();
		$this->assertSame( Donation::STATUS_DELETED, $donation->getStatus() );
	}

	public function cancellableStatusProvider() {
		return [
			[ Donation::STATUS_NEW ],
			[ Donation::STATUS_MODERATION ],
		];
	}

}