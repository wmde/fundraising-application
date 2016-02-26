<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\UseCases\CancelDonation;

/**
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class CancelDonationResponse {

	private $donationId;
	private $isSuccess;

	public function __construct( int $donationId, bool $isSuccess /* TODO: the smag should go here? */ ) {
		$this->donationId = $donationId;
		$this->isSuccess = $isSuccess;
	}

	public function getDonationId(): int {
		return $this->donationId;
	}

	public function cancellationWasSuccessful(): bool {
		return $this->isSuccess;
	}

}
