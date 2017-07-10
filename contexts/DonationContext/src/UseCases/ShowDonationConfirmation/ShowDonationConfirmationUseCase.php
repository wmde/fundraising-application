<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\DonationContext\UseCases\ShowDonationConfirmation;

use WMDE\Fundraising\Frontend\DonationContext\Authorization\DonationAuthorizer;
use WMDE\Fundraising\Frontend\DonationContext\Authorization\DonationTokenFetcher;
use WMDE\Fundraising\Frontend\DonationContext\Domain\Model\Donation;
use WMDE\Fundraising\Frontend\DonationContext\Domain\Repositories\DonationRepository;
use WMDE\Fundraising\Frontend\DonationContext\Domain\Repositories\GetDonationException;

/**
 * @license GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class ShowDonationConfirmationUseCase {

	private $authorizer;
	private $tokenFetcher;
	private $donationRepository;

	public function __construct( DonationAuthorizer $authorizer, DonationTokenFetcher $tokenFetcher,
								 DonationRepository $donationRepository ) {
		$this->authorizer = $authorizer;
		$this->tokenFetcher = $tokenFetcher;
		$this->donationRepository = $donationRepository;
	}

	public function showConfirmation( ShowDonationConfirmationRequest $request ): ShowDonationConfirmationResponse {
		if ( $this->authorizer->canAccessDonation( $request->getDonationId() ) ) {
			$donation = $this->getDonationById( $request->getDonationId() );

			if ( $donation !== null ) {
				return ShowDonationConfirmationResponse::newValidResponse(
					$donation,
					$this->tokenFetcher->getTokens( $request->getDonationId() )->getUpdateToken()
				);
			}
		}

		return ShowDonationConfirmationResponse::newNotAllowedResponse();
	}

	private function getDonationById( int $donationId ): ?Donation {
		try {
			return $this->donationRepository->getDonationById( $donationId );
		}
		catch ( GetDonationException $ex ) {
			return null;
		}
	}

}