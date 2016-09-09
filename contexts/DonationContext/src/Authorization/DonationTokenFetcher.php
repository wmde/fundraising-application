<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\DonationContext\Authorization;

/**
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
interface DonationTokenFetcher {

	/**
	 * @param int $donationId
	 *
	 * @return DonationTokens
	 * @throws DonationTokenFetchingException
	 */
	public function getTokens( int $donationId ): DonationTokens;

}
