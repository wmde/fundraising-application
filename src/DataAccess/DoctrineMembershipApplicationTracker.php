<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\DataAccess;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\ORMException;
use WMDE\Fundraising\Entities\MembershipApplication;
use WMDE\Fundraising\Frontend\Infrastructure\MembershipApplicationTracker;
use WMDE\Fundraising\Frontend\Infrastructure\MembershipApplicationTrackingException;
use WMDE\Fundraising\Frontend\Infrastructure\MembershipApplicationTrackingInfo;

/**
 * @licence GNU GPL v2+
 * @author Kai Nissen < kai.nissen@wikimedia.de >
 */
class DoctrineMembershipApplicationTracker implements MembershipApplicationTracker {

	private $entityManager;

	public function __construct( EntityManager $entityManager ) {
		$this->entityManager = $entityManager;
	}

	public function trackApplication( int $applicationId, MembershipApplicationTrackingInfo $trackingInfo ) {
		$application = $this->getApplicationById( $applicationId );

		$data = $application->getDecodedData();
		$data['confirmationPageCampaign'] = $trackingInfo->getCampaignCode();
		$data['confirmationPage'] = $trackingInfo->getKeyword();
		$application->encodeAndSetData( $data );

		$this->persistApplication( $application );
	}

	private function getApplicationById( int $applicationId ): MembershipApplication {
		try {
			$application = $this->entityManager->find( MembershipApplication::class, $applicationId );
		}
		catch ( ORMException $ex ) {
			// TODO: might want to log failure here
			throw new MembershipApplicationTrackingException( 'Membership application could not be accessed' );
		}

		if ( $application === null ) {
			throw new MembershipApplicationTrackingException( 'Membership application does not exist' );
		}

		return $application;
	}

	private function persistApplication( MembershipApplication $application ) {
		try {
			$this->entityManager->persist( $application );
			$this->entityManager->flush();
		}
		catch ( ORMException $ex ) {
			throw new MembershipApplicationTrackingException( 'Failed to persist membership application' );
		}
	}

}
