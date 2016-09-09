<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\MembershipContext\Authorization;

/**
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
interface ApplicationAuthorizer {

	/**
	 * Should return false on infrastructure failure.
	 */
	public function canModifyApplication( int $applicationId ): bool;

	/**
	 * Should return false on infrastructure failure.
	 */
	public function canAccessApplication( int $applicationId ): bool;

}
