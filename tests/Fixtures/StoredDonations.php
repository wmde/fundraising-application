<?php
declare( strict_types=1 );

namespace WMDE\Fundraising\Frontend\Tests\Fixtures;

use WMDE\Euro\Euro;
use WMDE\Fundraising\DonationContext\Domain\Model\Donation;
use WMDE\Fundraising\DonationContext\Tests\Data\ValidDonation;
use WMDE\Fundraising\DonationContext\Tests\Data\ValidPayments;
use WMDE\Fundraising\Frontend\Authentication\AuthenticationBoundedContext;
use WMDE\Fundraising\Frontend\Authentication\OldStyleTokens\AuthenticationToken;
use WMDE\Fundraising\Frontend\Factories\FunFunFactory;
use WMDE\Fundraising\PaymentContext\Domain\Model\Payment;
use WMDE\Fundraising\PaymentContext\Domain\Model\PaymentInterval;
use WMDE\Fundraising\PaymentContext\Domain\Model\PaymentReferenceCode;
use WMDE\Fundraising\PaymentContext\Domain\Model\SofortPayment;

/**
 * This is a fixture class that stores donations and payments from data fixtures
 */
class StoredDonations {

	public const DEFAULT_UPDATE_TOKEN = 'b5b249c8beefb986faf8d186a3f16e86ef509ab2';

	public function __construct( private readonly FunFunFactory $factory ) {
	}

	public function newStoredIncompleteCreditCardDonation( string $updateToken ): Donation {
		$this->persistPayment( ValidPayments::newCreditCardPayment() );
		$donation = $this->persistDonation( ValidDonation::newIncompleteCreditCardDonation() );
		$this->persistToken( $donation->getId(), $updateToken );
		return $donation;
	}

	public function newStoredIncompleteSofortDonation( string $updateToken = self::DEFAULT_UPDATE_TOKEN ): Donation {
		$this->persistPayment( SofortPayment::create(
			5,
			Euro::newFromFloat( 100 ),
			PaymentInterval::OneTime,
			PaymentReferenceCode::newFromString( ValidPayments::PAYMENT_BANK_TRANSFER_CODE )
		) );
		$donation = $this->persistDonation( ValidDonation::newIncompleteSofortDonation() );
		$this->persistToken( $donation->getId(), $updateToken );
		return $donation;
	}

	public function newStoredCompleteSofortDonation( string $updateToken = self::DEFAULT_UPDATE_TOKEN ): Donation {
		$this->persistPayment( ValidPayments::newCompletedSofortPayment() );
		$donation = $this->persistDonation( ValidDonation::newIncompleteSofortDonation() );
		$this->persistToken( $donation->getId(), $updateToken, $updateToken );
		return $donation;
	}

	public function newStoredIncompletePayPalDonation( string $updateToken = self::DEFAULT_UPDATE_TOKEN ): Donation {
		$this->persistPayment( ValidPayments::newPayPalPayment() );
		$donation = $this->persistDonation( ValidDonation::newIncompletePayPalDonation() );
		$this->persistToken( $donation->getId(), $updateToken, $updateToken );
		return $donation;
	}

	public function newStoredCompletePayPalDonation( string $updateToken = self::DEFAULT_UPDATE_TOKEN ): Donation {
		$this->persistPayment( ValidPayments::newBookedPayPalPayment() );
		$donation = $this->persistDonation( ValidDonation::newBookedPayPalDonation() );
		$this->persistToken( $donation->getId(), $updateToken, $updateToken );
		return $donation;
	}

	public function newStoredDirectDebitDonation( string $updateToken = self::DEFAULT_UPDATE_TOKEN ): Donation {
		$payment = ValidPayments::newDirectDebitPayment();
		$this->persistPayment( $payment );
		$donation = $this->persistDonation( ValidDonation::newDirectDebitDonation() );
		$this->persistToken( $donation->getId(), $updateToken, $updateToken );
		return $donation;
	}

	public function newStoredIncompleteAnonymousPayPalDonation( string $updateToken ): Donation {
		$payment = ValidPayments::newPayPalPayment();
		$this->persistPayment( $payment );
		$donation = $this->persistDonation( ValidDonation::newIncompleteAnonymousPayPalDonation() );
		$this->persistToken( $donation->getId(), $updateToken, $updateToken );
		return $donation;
	}

	public function newUpdatableDirectDebitDonation( string $updateToken ): Donation {
		return $this->newStoredDirectDebitDonation( $updateToken );
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

	private function persistToken( int $donationId, string $updateToken = '', string $accessToken = '' ): void {
		$em = $this->factory->getEntityManager();
		$em->persist( new AuthenticationToken(
			$donationId,
			AuthenticationBoundedContext::Donation,
			$accessToken,
			$updateToken,
			null
		) );
		$em->flush();
	}

}
