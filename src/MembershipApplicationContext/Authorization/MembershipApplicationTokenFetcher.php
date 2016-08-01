<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\MembershipApplicationContext\Authorization;

/**
 * @licence GNU GPL v2+
 * @author Kai Nissen < kai.nissen@wikimedia.de >
 */
interface MembershipApplicationTokenFetcher {

	/**
	 * @param int $membershipApplicationId
	 *
	 * @return MembershipApplicationTokens
	 * @throws MembershipApplicationTokenFetchingException
	 */
	public function getTokens( int $membershipApplicationId ): MembershipApplicationTokens;

}
