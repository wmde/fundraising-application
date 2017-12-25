<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\MembershipContext\Tests\Fixtures;

use WMDE\Fundraising\Frontend\MembershipContext\Authorization\ApplicationTokenFetcher;
use WMDE\Fundraising\Frontend\MembershipContext\Authorization\ApplicationTokenFetchingException;
use WMDE\Fundraising\Frontend\MembershipContext\Authorization\MembershipApplicationTokens;

/**
 * @licence GNU GPL v2+
 * @author Kai Nissen < kai.nissen@wikimedia.de >
 */
class FixedApplicationTokenFetcher implements ApplicationTokenFetcher {

	public const ACCESS_TOKEN = 'testAccessToken';
	public const UPDATE_TOKEN = 'testUpdateToken';

	public static function newWithDefaultTokens(): self {
		return new self(
			new MembershipApplicationTokens(
				self::ACCESS_TOKEN,
				self::UPDATE_TOKEN
			)
		);
	}

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
