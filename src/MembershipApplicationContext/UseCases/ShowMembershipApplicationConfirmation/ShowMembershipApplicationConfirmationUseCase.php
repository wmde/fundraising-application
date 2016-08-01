<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\MembershipApplicationContext\UseCases\ShowMembershipApplicationConfirmation;

use WMDE\Fundraising\Frontend\DonatingContext\Domain\Repositories\GetDonationException;
use WMDE\Fundraising\Frontend\MembershipApplicationContext\Authorization\MembershipApplicationAuthorizer;
use WMDE\Fundraising\Frontend\MembershipApplicationContext\Authorization\MembershipApplicationTokenFetcher;
use WMDE\Fundraising\Frontend\MembershipApplicationContext\Domain\Repositories\MembershipApplicationRepository;

/**
 * @license GNU GPL v2+
 * @author Kai Nissen < kai.nissen@wikimedia.de >
 */
class ShowMembershipApplicationConfirmationUseCase {

	private $authorizer;
	private $repository;
	/**
	 * @var MembershipApplicationTokenFetcher
	 */
	private $tokenFetcher;

	public function __construct( MembershipApplicationAuthorizer $authorizer, MembershipApplicationRepository $repository,
								 MembershipApplicationTokenFetcher $tokenFetcher ) {
		$this->authorizer = $authorizer;
		$this->repository = $repository;
		$this->tokenFetcher = $tokenFetcher;
	}

	public function showConfirmation( ShowMembershipAppConfirmationRequest $request ): ShowMembershipAppConfirmationResponse {
		if ( $this->authorizer->canAccessApplication( $request->getApplicationId() ) ) {
			$donation = $this->getMembershipApplicationById( $request->getApplicationId() );

			if ( $donation !== null ) {
				return ShowMembershipAppConfirmationResponse::newValidResponse(
					$donation,
					$this->tokenFetcher->getTokens( $request->getApplicationId() )->getUpdateToken()
				);
			}
		}

		return ShowMembershipAppConfirmationResponse::newNotAllowedResponse();
	}

	private function getMembershipApplicationById( int $donationId ) {
		try {
			return $this->repository->getApplicationById( $donationId );
		}
		catch ( GetDonationException $ex ) {
			return null;
		}
	}

}