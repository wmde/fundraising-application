<?php

declare(strict_types = 1);

namespace WMDE\Fundraising\Frontend\UseCases\CancelDonation;

/**
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class CancelDonationRequest {

	public $limit;
	public $donationToken;
	public $donationUpdateToken;

	public function __construct( string $donationId, string $donationToken, string $donationUpdateToken ) {
		$this->donationId = $donationId;
		$this->donationToken = $donationToken;
		$this->donationUpdateToken = $donationUpdateToken;
	}

	public function getLimit(): string {
		return $this->limit;
	}

	public function getDonationToken(): string {
		return $this->donationToken;
	}

	public function getDonationUpdateToken(): string {
		return $this->donationUpdateToken;
	}

}
