<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\MembershipContext\UseCases\ShowApplicationConfirmation;

use WMDE\Fundraising\Frontend\MembershipContext\Authorization\ApplicationAuthorizer;
use WMDE\Fundraising\Frontend\MembershipContext\Authorization\ApplicationTokenFetcher;
use WMDE\Fundraising\Frontend\MembershipContext\Domain\Repositories\ApplicationPurgedException;
use WMDE\Fundraising\Frontend\MembershipContext\Domain\Repositories\ApplicationRepository;
use WMDE\Fundraising\Frontend\MembershipContext\Domain\Repositories\GetMembershipApplicationException;

/**
 * @license GNU GPL v2+
 * @author Kai Nissen < kai.nissen@wikimedia.de >
 */
class ShowApplicationConfirmationUseCase {

	private $authorizer;
	private $repository;
	private $tokenFetcher;

	public function __construct( ApplicationAuthorizer $authorizer, ApplicationRepository $repository,
								 ApplicationTokenFetcher $tokenFetcher ) {
		$this->authorizer = $authorizer;
		$this->repository = $repository;
		$this->tokenFetcher = $tokenFetcher;
	}

	public function showConfirmation( ShowAppConfirmationRequest $request ): ShowApplicationConfirmationResponse {
		if ( !$this->authorizer->canAccessApplication( $request->getApplicationId() ) ) {
			return ShowApplicationConfirmationResponse::newNotAllowedResponse();
		}

		try {
			$application = $this->repository->getApplicationById( $request->getApplicationId() );
		}
		catch ( ApplicationPurgedException $ex ) {
			// TODO: success response without the Application
			return ShowApplicationConfirmationResponse::newNotAllowedResponse();
		}
		catch ( GetMembershipApplicationException $ex ) {
			return ShowApplicationConfirmationResponse::newNotAllowedResponse();
		}

		return ShowApplicationConfirmationResponse::newValidResponse(
			$application, // TODO: use DTO instead of Entity (currently violates the architecture)
			$this->tokenFetcher->getTokens( $request->getApplicationId() )->getUpdateToken()
		);
	}

}