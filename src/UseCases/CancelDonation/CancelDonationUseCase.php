<?php

declare(strict_types = 1);

namespace WMDE\Fundraising\Frontend\UseCases\CancelDonation;

/**
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class CancelDonationUseCase {

	public function cancelDonation( CancelDonationRequest $cancellationRequest ) {
		// TODO: update donation status
		// TODO: add log message to spenden.data['log']
		// TODO: reset spenden_stamp in cookie
		// TODO: send cancellation confirmation email
	}

}
