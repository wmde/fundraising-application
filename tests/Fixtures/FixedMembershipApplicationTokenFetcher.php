<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\Fixtures;

use WMDE\Fundraising\Frontend\Infrastructure\MembershipApplicationTokenFetcher;
use WMDE\Fundraising\Frontend\Infrastructure\MembershipApplicationTokenFetchingException;
use WMDE\Fundraising\Frontend\Infrastructure\MembershipApplicationTokens;

/**
 * @licence GNU GPL v2+
 * @author Kai Nissen < kai.nissen@wikimedia.de >
 */
class FixedMembershipApplicationTokenFetcher implements MembershipApplicationTokenFetcher {

	private $tokens;

	public function __construct( MembershipApplicationTokens $tokens ) {
		$this->tokens = $tokens;
	}

	/**
	 * @param int $applicationId
	 *
	 * @return MembershipApplicationTokens
	 * @throws MembershipApplicationTokenFetchingException
	 */
	public function getTokens( int $applicationId ): MembershipApplicationTokens {
		return $this->tokens;
	}

}
