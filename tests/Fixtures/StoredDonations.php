<?php
declare( strict_types=1 );

namespace WMDE\Fundraising\Frontend\Tests\Fixtures;

use WMDE\Fundraising\DonationContext\DataAccess\DoctrineEntities\Donation as DoctrineDonation;
use WMDE\Fundraising\DonationContext\DataAccess\DonationData;
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

	public function newUpdatableDirectDebitDonation( string $updateToken ): Donation {
		$donation = $this->newStoredDirectDebitDonation();

		$entityManager = $this->factory->getEntityManager();
		/**
		 * @var DoctrineDonation $doctrineDonation
		 */
		$doctrineDonation = $entityManager->getRepository( DoctrineDonation::class )->find( $donation->getId() );

		$doctrineDonation->modifyDataObject( static function ( DonationData $data ) use ( $updateToken ): void {
			$data->setUpdateToken( $updateToken );
			$data->setUpdateTokenExpiry( date( 'Y-m-d H:i:s', time() + 60 * 60 ) );
		} );

		$entityManager->persist( $doctrineDonation );
		$entityManager->flush();
		return $donation;
	}

	private function persistPayment( Payment $payment ): void {
		$em = $this->factory->getEntityManager();
		$em->persist( $payment );
		$em->flush();
	}

}
