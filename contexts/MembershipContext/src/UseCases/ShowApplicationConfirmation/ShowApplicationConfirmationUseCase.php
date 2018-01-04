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
			$this->presenter->presentAccessViolation();
			return;
		}

		try {
			$application = $this->repository->getApplicationById( $request->getApplicationId() );
		}
		catch ( ApplicationPurgedException $ex ) {
			$this->presenter->presentApplicationWasPurged();
			return;
		}
		catch ( GetMembershipApplicationException $ex ) {
			$this->presenter->presentTechnicalError( 'A database error occurred' );
			return;
		}

		$this->presenter->presentConfirmation(
			$application, // TODO: use DTO instead of Entity (currently violates the architecture)
			$this->tokenFetcher->getTokens( $request->getApplicationId() )->getUpdateToken()
		);
	}

}