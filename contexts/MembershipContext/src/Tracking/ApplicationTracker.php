<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\MembershipContext\Tracking;

/**
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 * @author Kai Nissen < kai.nissen@wikimedia.de >
 */
interface ApplicationTracker {

	/**
	 * @throws ApplicationTrackingException
	 */
	public function trackApplication( int $applicationId, MembershipApplicationTrackingInfo $trackingInfo ): void;

}
