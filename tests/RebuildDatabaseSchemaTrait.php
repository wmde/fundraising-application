<?php
declare( strict_types=1 );

namespace WMDE\Fundraising\Frontend\Tests;

use WMDE\Fundraising\DonationContext\Domain\Model\DonationId;
use WMDE\Fundraising\Frontend\Factories\FunFunFactory;
use WMDE\Fundraising\MembershipContext\Domain\Model\MembershipId;
use WMDE\Fundraising\PaymentContext\Domain\Model\PaymentId;

trait RebuildDatabaseSchemaTrait {
	public static function rebuildDatabaseSchema( FunFunFactory $factory ): void {
		$entityManager = $factory->getEntityManager();
		$schemaCreator = new SchemaCreator( $entityManager );

		try {
			$schemaCreator->dropSchema();
		} catch ( \Exception $ex ) {
		}

		$schemaCreator->createSchema();
		$entityManager->persist( new PaymentId() );
		$entityManager->persist( new DonationId() );
		$entityManager->persist( new MembershipId() );
		$entityManager->flush();
	}

}
