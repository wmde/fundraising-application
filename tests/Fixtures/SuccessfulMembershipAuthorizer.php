<?php
declare( strict_types=1 );

namespace WMDE\Fundraising\Frontend\Tests\Fixtures;

use WMDE\Fundraising\MembershipContext\Authorization\MembershipAuthorizationChecker;

class SuccessfulMembershipAuthorizer implements MembershipAuthorizationChecker {

	public function canModifyMembership( int $membershipId ): bool {
		return true;
	}

	public function canAccessMembership( int $membershipId ): bool {
		return true;
	}
}
