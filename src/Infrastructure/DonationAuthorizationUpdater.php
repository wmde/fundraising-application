<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Infrastructure;

/**
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
interface DonationAuthorizationUpdater {

	/**
	 * @throws AuthorizationUpdateException
	 */
	public function allowModificationViaToken( int $donationId, string $token, \DateTime $expiry );

	/**
	 * @throws AuthorizationUpdateException
	 */
	public function allowAccessViaToken( int $donationId, string $accessToken );

}
