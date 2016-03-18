<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\UseCases\ShowDonationConfirmation;

/**
 * @license GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class ShowDonationConfirmationUseCase {

	public function showConfirmation( ShowDonationConfirmationRequest $request ): ShowDonationConfirmationResponse {
		// TODO: verify access token
		// TODO: retrieve donation
	}

}