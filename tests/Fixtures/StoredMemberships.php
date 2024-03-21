<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\Fixtures;

use WMDE\Fundraising\DonationContext\Tests\Data\ValidPayments;
use WMDE\Fundraising\Frontend\Factories\FunFunFactory;
use WMDE\Fundraising\MembershipContext\DataAccess\DoctrineEntities\MembershipApplication as DoctrineMembershipApplication;
use WMDE\Fundraising\MembershipContext\DataAccess\MembershipApplicationData;
use WMDE\Fundraising\MembershipContext\Tests\Fixtures\ValidMembershipApplication;
use WMDE\Fundraising\PaymentContext\Domain\Model\Payment;

class StoredMemberships {
	public function __construct( private readonly FunFunFactory $factory ) {
	}

	public function storeValidMembershipApplication( string $updateToken ): DoctrineMembershipApplication {
		$this->persistPayment( ValidPayments::newDirectDebitPayment() );
		$application = ValidMembershipApplication::newDoctrineEntity();

		$application->modifyDataObject( static function ( MembershipApplicationData $data ) use ( $updateToken ): void {
			$data->setUpdateToken( $updateToken );
		} );

		$this->persistApplication( $application );

		return $application;
	}

	private function persistApplication( DoctrineMembershipApplication $membershipApplication ): void {
		$entityManager = $this->factory->getEntityManager();
		$entityManager->persist( $membershipApplication );
		$entityManager->flush();
	}

	private function persistPayment( Payment $payment ): void {
		$entityManager = $this->factory->getEntityManager();
		$entityManager->persist( $payment );
		$entityManager->flush();
	}
}
