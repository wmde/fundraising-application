<?php
declare( strict_types=1 );

namespace WMDE\Fundraising\Frontend\Tests\Fixtures;

use WMDE\Euro\Euro;
use WMDE\Fundraising\DonationContext\Domain\Model\Donation;
use WMDE\Fundraising\DonationContext\Tests\Data\ValidDonation;
use WMDE\Fundraising\DonationContext\Tests\Data\ValidPayments;
use WMDE\Fundraising\Frontend\Factories\FunFunFactory;
use WMDE\Fundraising\PaymentContext\Domain\Model\Payment;
use WMDE\Fundraising\PaymentContext\Domain\Model\PaymentInterval;
use WMDE\Fundraising\PaymentContext\Domain\Model\PaymentReferenceCode;
use WMDE\Fundraising\PaymentContext\Domain\Model\SofortPayment;

/**
 * This is a fixture class that stores donations and payments from data fixtures
 */
class StoredDonations {

	public function __construct( private FunFunFactory $factory ) {
	}

	public function newStoredIncompleteCreditCardDonation( string $updateToken ): Donation {
		$this->setDonationTokenGenerator( $updateToken );
		$this->persistPayment( ValidPayments::newCreditCardPayment() );
		return $this->persistDonation( ValidDonation::newIncompleteCreditCardDonation() );
	}

	public function newStoredIncompleteSofortDonation(): Donation {
		$this->persistPayment( SofortPayment::create(
			5,
			Euro::newFromFloat( 100 ),
			PaymentInterval::OneTime,
			PaymentReferenceCode::newFromString( ValidPayments::PAYMENT_BANK_TRANSFER_CODE )
		) );
		return $this->persistDonation( ValidDonation::newIncompleteSofortDonation() );
	}

	public function newStoredCompleteSofortDonation(): Donation {
		$this->persistPayment( ValidPayments::newCompletedSofortPayment() );
		return $this->persistDonation( ValidDonation::newIncompleteSofortDonation() );
	}

	public function newStoredIncompletePayPalDonation(): Donation {
		$this->persistPayment( ValidPayments::newPayPalPayment() );
		return $this->persistDonation( ValidDonation::newIncompletePayPalDonation() );
	}

	public function newStoredDirectDebitDonation(): Donation {
		$payment = ValidPayments::newDirectDebitPayment();
		$this->persistPayment( $payment );
		return $this->persistDonation( ValidDonation::newDirectDebitDonation() );
	}

	public function newStoredIncompleteAnonymousPayPalDonation(): Donation {
		$payment = ValidPayments::newPayPalPayment();
		$this->persistPayment( $payment );
		return $this->persistDonation( ValidDonation::newIncompleteAnonymousPayPalDonation() );
	}

	public function newUpdatableDirectDebitDonation( string $updateToken ): Donation {
		$this->setDonationTokenGenerator( $updateToken );
		return $this->newStoredDirectDebitDonation();
	}

	private function persistDonation( Donation $donation ): Donation {
		$this->factory->getDonationRepository()->storeDonation( $donation );
		return $donation;
	}

	private function persistPayment( Payment $payment ): void {
		$em = $this->factory->getEntityManager();
		$em->persist( $payment );
		$em->flush();
	}

	private function setDonationTokenGenerator( string $updateToken ): void {
		$this->factory->setDonationTokenGenerator( new FixedTokenGenerator(
			$updateToken,
			\DateTime::createFromFormat( 'Y-m-d H:i:s', '2039-12-31 23:59:59' )
		) );
	}

}
