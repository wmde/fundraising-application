<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\MembershipApplicationContext\Tests\Fixtures;

use WMDE\Fundraising\Frontend\MembershipApplicationContext\Authorization\ApplicationTokenFetcher;
use WMDE\Fundraising\Frontend\MembershipApplicationContext\Authorization\ApplicationTokenFetchingException;
use WMDE\Fundraising\Frontend\MembershipApplicationContext\Authorization\MembershipApplicationTokens;

/**
 * @licence GNU GPL v2+
 * @author Kai Nissen < kai.nissen@wikimedia.de >
 */
class FixedApplicationTokenFetcher implements ApplicationTokenFetcher {

	private $tokens;

	public function __construct( MembershipApplicationTokens $tokens ) {
		$this->tokens = $tokens;
	}

	/**
	 * @param int $applicationId
	 *
	 * @return MembershipApplicationTokens
	 * @throws ApplicationTokenFetchingException
	 */
	public function getTokens( int $applicationId ): MembershipApplicationTokens {
		return $this->tokens;
	}

}
