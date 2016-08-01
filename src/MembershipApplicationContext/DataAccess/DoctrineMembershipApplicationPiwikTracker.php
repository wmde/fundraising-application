<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\MembershipApplicationContext\DataAccess;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\ORMException;
use WMDE\Fundraising\Entities\MembershipApplication;
use WMDE\Fundraising\Frontend\MembershipApplicationContext\Tracking\MembershipApplicationPiwikTracker;
use WMDE\Fundraising\Frontend\MembershipApplicationContext\Tracking\MembershipApplicationPiwikTrackingException;

/**
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class DoctrineMembershipApplicationPiwikTracker implements MembershipApplicationPiwikTracker {

	private $entityManager;

	public function __construct( EntityManager $entityManager ) {
		$this->entityManager = $entityManager;
	}

	public function trackApplication( int $applicationId, string $trackingString ) {
		$application = $this->getApplicationById( $applicationId );

		$application->setTracking( $trackingString );

		$this->persistApplication( $application );
	}

	private function getApplicationById( int $applicationId ): MembershipApplication {
		try {
			$application = $this->entityManager->find( MembershipApplication::class, $applicationId );
		}
		catch ( ORMException $ex ) {
			// TODO: might want to log failure here
			throw new MembershipApplicationPiwikTrackingException( 'Membership application could not be accessed' );
		}

		if ( $application === null ) {
			throw new MembershipApplicationPiwikTrackingException( 'Membership application does not exist' );
		}

		return $application;
	}

	private function persistApplication( MembershipApplication $application ) {
		try {
			$this->entityManager->persist( $application );
			$this->entityManager->flush();
		}
		catch ( ORMException $ex ) {
			throw new MembershipApplicationPiwikTrackingException( 'Failed to persist membership application' );
		}
	}

}
