<?php
declare( strict_types=1 );

namespace WMDE\Fundraising\Frontend\Authentication\OldStyleTokens;

use WMDE\Fundraising\DonationContext\Authorization\DonationTokenFetcher;
use WMDE\Fundraising\DonationContext\Authorization\DonationTokenFetchingException;
use WMDE\Fundraising\DonationContext\Authorization\DonationTokens;
use WMDE\Fundraising\Frontend\Authentication\AuthenticationBoundedContext;

/**
 * @deprecated The use case should not use this. Instead, the Controller/presenter should get the UpdateTokens
 * @codeCoverageIgnore
 */
class LegacyDonationTokenFetcher implements DonationTokenFetcher {
	private TokenRepository $repository;

	public function __construct( TokenRepository $repository ) {
		$this->repository = $repository;
	}

	public function getTokens( int $donationId ): DonationTokens {
		$token = $this->repository->getTokenById( $donationId, AuthenticationBoundedContext::Donation );

		try {
			return new DonationTokens(
				$token->getAccessToken(),
				$token->getUpdateToken()
			);
		} catch ( \UnexpectedValueException $e ) {
			throw new DonationTokenFetchingException( $e->getMessage(), $e );
		}
	}

}
