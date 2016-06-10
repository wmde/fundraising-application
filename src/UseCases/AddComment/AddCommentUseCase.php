<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\UseCases\AddComment;

use WMDE\Fundraising\Frontend\Domain\Model\DonationComment;
use WMDE\Fundraising\Frontend\Domain\Repositories\DonationRepository;
use WMDE\Fundraising\Frontend\Domain\Repositories\GetDonationException;
use WMDE\Fundraising\Frontend\Domain\Repositories\StoreDonationException;
use WMDE\Fundraising\Frontend\Infrastructure\DonationAuthorizer;

/**
 * @license GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class AddCommentUseCase {

	private $donationRepository;
	private $authorizationService;

	public function __construct( DonationRepository $repository, DonationAuthorizer $authorizationService ) {
		$this->donationRepository = $repository;
		$this->authorizationService = $authorizationService;
	}

	public function addComment( AddCommentRequest $addCommentRequest ): AddCommentResponse {
		if ( !$this->requestIsAllowed( $addCommentRequest ) ) {
			return AddCommentResponse::newFailureResponse( 'Authorization failed' );
		}

		try {
			$donation = $this->donationRepository->getDonationById( $addCommentRequest->getDonationId() );
		}
		catch ( GetDonationException $ex ) {
			return AddCommentResponse::newFailureResponse( 'Could not retrieve donation' );
		}

		if ( $donation === null ) {
			return AddCommentResponse::newFailureResponse( 'Donation not found' );
		}

		if ( $donation->getComment() !== null ) {
			return AddCommentResponse::newFailureResponse( 'A comment has already been added' );
		}

		$donation->addComment( $this->newCommentFromRequest( $addCommentRequest ) );

		try {
			$this->donationRepository->storeDonation( $donation );
		}
		catch ( StoreDonationException $ex ) {
			return AddCommentResponse::newFailureResponse( 'Could not add comment to donation' );
		}

		return AddCommentResponse::newSuccessResponse();
	}

	private function requestIsAllowed( AddCommentRequest $addCommentRequest ): bool {
		return $this->authorizationService->userCanModifyDonation( $addCommentRequest->getDonationId() );
	}

	private function newCommentFromRequest( AddCommentRequest $request ): DonationComment {
		return new DonationComment(
			$request->getCommentText(),
			$request->isPublic(),
			$request->getAuthorDisplayName()
		);
	}

}