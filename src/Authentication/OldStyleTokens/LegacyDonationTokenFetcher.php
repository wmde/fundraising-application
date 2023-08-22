<?php
declare( strict_types=1 );

namespace WMDE\Fundraising\Frontend\Authentication\OldStyleTokens;

use WMDE\Fundraising\DonationContext\Authorization\DonationTokenFetcher;
use WMDE\Fundraising\DonationContext\Authorization\DonationTokenFetchingException;
use WMDE\Fundraising\DonationContext\Authorization\DonationTokens;
use WMDE\Fundraising\Frontend\Authentication\AuthenticationBoundedContext;

/**
 * @deprecated The use case should not use this. Instead, the Controller/presenter should get the UpdateTokens
 */
class LegacyDonationTokenFetcher implements DonationTokenFetcher {
	private TokenRepository $repository;

	public function __construct( TokenRepository $repository ) {
		$this->repository = $repository;
	}

	public function getTokens( int $donationId ): DonationTokens {
		$token = $this->repository->getTokenById( $donationId, AuthenticationBoundedContext::Donation );
		if ( $token === null ) {
			throw new DonationTokenFetchingException( sprintf( 'Could not find donation with ID "%d"', $donationId ) );
		}

		try {
			return new DonationTokens(
				(string)$token->accessToken,
				(string)$token->updateToken
			);
		} catch ( \UnexpectedValueException $e ) {
			throw new DonationTokenFetchingException( $e->getMessage(), $e );
		}
	}

}
