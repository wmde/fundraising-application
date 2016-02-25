<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\UseCases\CancelDonation;

/**
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class CancelDonationResponse {

	private $donationId;

	public function __construct( int $donationId /* TODO: the smag should go here? */ ) {
		$this->donationId = $donationId;
	}

	public function getDonationId(): int {
		return $this->donationId;
	}

	public function cancellationWasSuccessful(): bool {
		return false;
	}

}
