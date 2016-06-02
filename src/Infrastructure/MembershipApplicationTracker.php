<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Infrastructure;

/**
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
interface MembershipApplicationTracker {

	// TODO: exception
	public function trackApplication( MembershipApplicationTrackingInfo $trackingInfo );

}
