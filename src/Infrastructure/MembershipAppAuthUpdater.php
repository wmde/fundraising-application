<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Infrastructure;

/**
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
interface MembershipAppAuthUpdater {

	/**
	 * @throws AuthorizationUpdateException
	 */
	public function allowModificationViaToken( int $applicationId, string $updateToken );

	/**
	 * @throws AuthorizationUpdateException
	 */
	public function allowAccessViaToken( int $applicationId, string $accessToken );

}
