<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\Fixtures;

use WMDE\Fundraising\Frontend\MembershipApplicationContext\Authorization\MembershipApplicationAuthorizer;

/**
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class SucceedingMembershipAuthorizer implements MembershipApplicationAuthorizer {

	public function canModifyApplication( int $applicationId ): bool {
		return true;
	}

	public function canAccessApplication( int $applicationId ): bool {
		return true;
	}

}
