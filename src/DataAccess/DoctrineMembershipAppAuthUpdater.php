<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\DataAccess;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\ORMException;
use WMDE\Fundraising\Entities\MembershipApplication;
use WMDE\Fundraising\Frontend\Infrastructure\AuthorizationUpdateException;
use WMDE\Fundraising\Frontend\Infrastructure\MembershipAppAuthUpdater;
use WMDE\Fundraising\Store\MembershipApplicationData;

/**
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class DoctrineMembershipAppAuthUpdater implements MembershipAppAuthUpdater {

	private $entityManager;

	public function __construct( EntityManager $entityManager ) {
		$this->entityManager = $entityManager;
	}

	/**
	 * @throws AuthorizationUpdateException
	 */
	public function allowModificationViaToken( int $applicationId, string $updateToken ) {
		$application = $this->getApplicationById( $applicationId );

		$application->modifyDataObject( function( MembershipApplicationData $data ) use ( $updateToken ) {
			$data->setUpdateToken( $updateToken );
		} );

		$this->persistApplication( $application );
	}

	private function getApplicationById( int $applicationId ): MembershipApplication {
		try {
			$application = $this->entityManager->find( MembershipApplication::class, $applicationId );
		}
		catch ( ORMException $ex ) {
			// TODO: might want to log failure here
			throw new AuthorizationUpdateException( 'MembershipApplication could not be accessed' );
		}

		if ( $application === null ) {
			throw new AuthorizationUpdateException( 'MembershipApplication does not exist' );
		}

		return $application;
	}

	private function persistApplication( MembershipApplication $application ) {
		try {
			$this->entityManager->persist( $application );
			$this->entityManager->flush();
		}
		catch ( ORMException $ex ) {
			throw new AuthorizationUpdateException( 'Failed to persist the membership application' );
		}
	}

	/**
	 * @throws AuthorizationUpdateException
	 */
	public function allowAccessViaToken( int $applicationId, string $accessToken ) {
		$application = $this->getApplicationById( $applicationId );

		$application->modifyDataObject( function( MembershipApplicationData $data ) use ( $accessToken ) {
			$data->setAccessToken( $accessToken );
		} );

		$this->persistApplication( $application );
	}

}
