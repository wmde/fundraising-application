<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\UseCases\ShowDonationConfirmation;

use WMDE\Fundraising\Frontend\Domain\Model\Donation;
use WMDE\Fundraising\Frontend\Domain\Repositories\DonationRepository;
use WMDE\Fundraising\Frontend\Infrastructure\DonationAuthorizer;

/**
 * @license GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class ShowDonationConfirmationUseCase {

	private $authorizer;
	private $donationRepository;

	public function __construct( DonationAuthorizer $authorizer, DonationRepository $donationRepository ) {
		$this->authorizer = $authorizer;
		$this->donationRepository = $donationRepository;
	}

	public function showConfirmation( ShowDonationConfirmationRequest $request ): ShowDonationConfirmationResponse {

		if ( $this->authorizer->canAccessDonation( $request->getDonationId() ) ) {
			// TODO: retrieve donation
			return ShowDonationConfirmationResponse::newValidResponse( new Donation() );
		}

		return ShowDonationConfirmationResponse::newNotAllowedResponse();
	}

}