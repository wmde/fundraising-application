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

	private $presenter;
	private $authorizer;
	private $repository;
	private $tokenFetcher;

	public function __construct( ShowApplicationConfirmationPresenter $presenter, ApplicationAuthorizer $authorizer,
		ApplicationRepository $repository, ApplicationTokenFetcher $tokenFetcher ) {
		$this->presenter = $presenter;
		$this->authorizer = $authorizer;
		$this->repository = $repository;
		$this->tokenFetcher = $tokenFetcher;
	}

	public function showConfirmation( ShowAppConfirmationRequest $request ): void {
		if ( !$this->authorizer->canAccessApplication( $request->getApplicationId() ) ) {
			// TODO: show access error
			$this->presenter->presentResponseModel( ShowApplicationConfirmationResponse::newNotAllowedResponse() );
			return;
		}

		try {
			$application = $this->repository->getApplicationById( $request->getApplicationId() );
		}
		catch ( ApplicationPurgedException $ex ) {
			// TODO: show application was purged
			$this->presenter->presentResponseModel( ShowApplicationConfirmationResponse::newNotAllowedResponse() );
			return;
		}
		catch ( GetMembershipApplicationException $ex ) {
			// TODO: show technical error
			$this->presenter->presentResponseModel( ShowApplicationConfirmationResponse::newNotAllowedResponse() );
			return;
		}

		$this->presenter->presentResponseModel(
			ShowApplicationConfirmationResponse::newValidResponse(
				$application, // TODO: use DTO instead of Entity (currently violates the architecture)
				$this->tokenFetcher->getTokens( $request->getApplicationId() )->getUpdateToken()
			)
		);
	}

}