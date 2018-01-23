<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\MembershipContext\DataAccess;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\ORMException;
use WMDE\Fundraising\Entities\MembershipApplication;
use WMDE\Fundraising\MembershipContext\Tracking\ApplicationPiwikTracker;
use WMDE\Fundraising\MembershipContext\Tracking\ApplicationPiwikTrackingException;

/**
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class DoctrineApplicationPiwikTracker implements ApplicationPiwikTracker {

	private $entityManager;

	public function __construct( EntityManager $entityManager ) {
		$this->entityManager = $entityManager;
	}

	public function trackApplication( int $applicationId, string $trackingString ): void {
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
			throw new ApplicationPiwikTrackingException( 'Membership application could not be accessed' );
		}

		if ( $application === null ) {
			throw new ApplicationPiwikTrackingException( 'Membership application does not exist' );
		}

		return $application;
	}

	private function persistApplication( MembershipApplication $application ): void {
		try {
			$this->entityManager->persist( $application );
			$this->entityManager->flush();
		}
		catch ( ORMException $ex ) {
			throw new ApplicationPiwikTrackingException( 'Failed to persist membership application' );
		}
	}

}
