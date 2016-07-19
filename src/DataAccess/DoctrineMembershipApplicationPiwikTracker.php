<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\DataAccess;

use WMDE\Fundraising\Entities\MembershipApplication;
use WMDE\Fundraising\Frontend\Infrastructure\MembershipApplicationPiwikTracker;
use WMDE\Fundraising\Frontend\Infrastructure\MembershipApplicationPiwikTrackingException;
use WMDE\Fundraising\Store\MembershipApplicationRepository;
use WMDE\Fundraising\Store\MembershipApplicationRepositoryException;

/**
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class DoctrineMembershipApplicationPiwikTracker implements MembershipApplicationPiwikTracker {

	private $applicationModifier;

	public function __construct( MembershipApplicationRepository $applicationModifier ) {
		$this->applicationModifier = $applicationModifier;
	}

	public function trackApplication( int $applicationId, string $trackingString ) {
		try {
			$this->applicationModifier->modifyApplication(
				$applicationId,
				function( MembershipApplication $application ) use ( $trackingString ) {
					$application->setTracking( $trackingString );
				}
			);
		}
		catch ( MembershipApplicationRepositoryException $ex ) {
			throw new MembershipApplicationPiwikTrackingException( 'Could not update membership application' );
		}
	}

}
