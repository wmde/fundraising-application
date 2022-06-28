<?php
declare( strict_types=1 );

namespace WMDE\Fundraising\Frontend\Tests\Fixtures;

use WMDE\Fundraising\DonationContext\Domain\Model\Donation;
use WMDE\Fundraising\DonationContext\Tests\Data\ValidDonation;
use WMDE\Fundraising\DonationContext\Tests\Data\ValidPayments;
use WMDE\Fundraising\Frontend\Factories\FunFunFactory;
use WMDE\Fundraising\PaymentContext\Domain\Model\Payment;

/**
 * This is a fixture class that stores donations and payments from data fixtures
 */
class StoredDonations {

	public function __construct( private FunFunFactory $factory ) {
	}

	public function newStoredDirectDebitDonation(): Donation {
		$payment = ValidPayments::newDirectDebitPayment();
		$this->persistPayment( $payment );
		$donation = ValidDonation::newDirectDebitDonation();
		$this->factory->getDonationRepository()->storeDonation( $donation );
		return $donation;
	}

	public function newStoredIncompleteAnonymousPayPalDonation(): Donation {
		$payment = ValidPayments::newPayPalPayment();
		$this->persistPayment( $payment );
		$donation = ValidDonation::newIncompleteAnonymousPayPalDonation();
		$this->factory->getDonationRepository()->storeDonation( $donation );
		return $donation;
	}

	private function persistPayment( Payment $payment ): void {
		$em = $this->factory->getEntityManager();
		$em->persist( $payment );
		$em->flush();
	}

}
