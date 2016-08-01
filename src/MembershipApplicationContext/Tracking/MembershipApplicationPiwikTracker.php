<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\MembershipApplicationContext\Tracking;

/**
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
interface MembershipApplicationPiwikTracker {

	/**
	 * @param int $applicationId
	 * @param string $trackingString
	 *
	 * @throws MembershipApplicationPiwikTrackingException
	 */
	public function trackApplication( int $applicationId, string $trackingString );

}
