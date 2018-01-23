<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\MembershipContext\Authorization;

/**
 * @licence GNU GPL v2+
 * @author Kai Nissen < kai.nissen@wikimedia.de >
 */
interface ApplicationTokenFetcher {

	/**
	 * @param int $membershipApplicationId
	 *
	 * @return MembershipApplicationTokens
	 * @throws ApplicationTokenFetchingException
	 */
	public function getTokens( int $membershipApplicationId ): MembershipApplicationTokens;

}
