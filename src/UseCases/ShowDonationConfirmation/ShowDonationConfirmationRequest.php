<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\UseCases\ShowDonationConfirmation;

/**
 * @license GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class ShowDonationConfirmationRequest {

	private $donationId;
	private $accessToken;

	public function __construct( int $donationId, string $accessToken ) {
		$this->donationId = $donationId;
		$this->accessToken = $accessToken;
	}

	public function getDonationId(): int {
		return $this->donationId;
	}

	public function getAccessToken(): string {
		return $this->accessToken;
	}

}