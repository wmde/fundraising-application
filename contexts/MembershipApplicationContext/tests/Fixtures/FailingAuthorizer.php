<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\MembershipApplicationContext\Tests\Fixtures;

use WMDE\Fundraising\Frontend\MembershipApplicationContext\Authorization\ApplicationAuthorizer;

/**
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class FailingAuthorizer implements ApplicationAuthorizer {

	public function canModifyApplication( int $applicationId ): bool {
		return false;
	}

	public function canAccessApplication( int $applicationId ): bool {
		return false;
	}

}