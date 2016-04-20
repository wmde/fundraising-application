<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\Fixtures;

use WMDE\Fundraising\Frontend\Infrastructure\MembershipApplicationAuthorizer;

/**
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class FailingMembershipAuthorizer implements MembershipApplicationAuthorizer {

	public function canModifyApplication( int $applicationId ): bool {
		return false;
	}

	public function canAccessApplication( int $applicationId ): bool {
		return false;
	}

}