<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\UseCases\CancelDonation;

/**
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class CancelDonationRequest {

	private $donationId;
	private $donationToken;
	private $donationUpdateToken;

	public function __construct( string $donationId, string $donationToken, string $donationUpdateToken ) {
		$this->donationId = $donationId;
		$this->donationToken = $donationToken;
		$this->donationUpdateToken = $donationUpdateToken;
	}

	public function getDonationId(): string {
		return $this->donationId;
	}

	public function getDonationToken(): string {
		return $this->donationToken;
	}

	public function getDonationUpdateToken(): string {
		return $this->donationUpdateToken;
	}

}
