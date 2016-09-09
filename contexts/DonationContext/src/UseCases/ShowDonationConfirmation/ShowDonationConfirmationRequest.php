<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\DonationContext\UseCases\ShowDonationConfirmation;

/**
 * @license GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class ShowDonationConfirmationRequest {

	private $donationId;

	public function __construct( int $donationId ) {
		$this->donationId = $donationId;
	}

	public function getDonationId(): int {
		return $this->donationId;
	}

}
