<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Infrastructure;

/**
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
interface AuthorizationUpdater {

	/**
	 * @throws AuthorizationUpdateException
	 */
	public function allowDonationModificationViaToken( int $donationId, string $token, \DateTime $expiry );

	/**
	 * @throws AuthorizationUpdateException
	 */
	public function allowDonationAccessViaToken( int $donationId, string $accessToken );

}
