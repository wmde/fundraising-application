<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\MembershipContext\UseCases\ShowMembershipApplicationConfirmation;

use WMDE\Fundraising\Frontend\MembershipContext\Authorization\ApplicationAuthorizer;
use WMDE\Fundraising\Frontend\MembershipContext\Authorization\ApplicationTokenFetcher;
use WMDE\Fundraising\Frontend\MembershipContext\Domain\Model\Application;
use WMDE\Fundraising\Frontend\MembershipContext\Domain\Repositories\ApplicationRepository;
use WMDE\Fundraising\Frontend\MembershipContext\Domain\Repositories\GetMembershipApplicationException;

/**
 * @license GNU GPL v2+
 * @author Kai Nissen < kai.nissen@wikimedia.de >
 */
class ShowMembershipApplicationConfirmationUseCase {

	private $authorizer;
	private $repository;
	/**
	 * @var ApplicationTokenFetcher
	 */
	private $tokenFetcher;

	public function __construct( ApplicationAuthorizer $authorizer, ApplicationRepository $repository,
								 ApplicationTokenFetcher $tokenFetcher ) {
		$this->authorizer = $authorizer;
		$this->repository = $repository;
		$this->tokenFetcher = $tokenFetcher;
	}

	public function showConfirmation( ShowMembershipAppConfirmationRequest $request ): ShowMembershipAppConfirmationResponse {
		if ( $this->authorizer->canAccessApplication( $request->getApplicationId() ) ) {
			$application = $this->getMembershipApplicationById( $request->getApplicationId() );

			if ( $application !== null ) {
				return ShowMembershipAppConfirmationResponse::newValidResponse(
					$application,
					$this->tokenFetcher->getTokens( $request->getApplicationId() )->getUpdateToken()
				);
			}
		}

		return ShowMembershipAppConfirmationResponse::newNotAllowedResponse();
	}

	private function getMembershipApplicationById( int $applicationId ): ?Application {
		try {
			return $this->repository->getApplicationById( $applicationId );
		}
		catch ( GetMembershipApplicationException $ex ) {
			return null;
		}
	}

}