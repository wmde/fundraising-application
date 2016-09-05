<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\DonatingContext\UseCases\CancelDonation;

/**
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class CancelDonationResponse {

	const SUCCESS = 'success';
	const FAILURE = 'failure';
	const MAIL_DELIVERY_FAILED = 'mail-not-send';

	private $donationId;
	private $state;

	public function __construct( int $donationId, string $state ) {
		$this->donationId = $donationId;
		$this->state = $state;
	}

	public function getDonationId(): int {
		return $this->donationId;
	}

	public function cancellationSucceeded(): bool {
		return $this->state !== self::FAILURE;
	}

	public function mailDeliveryFailed(): bool {
		return $this->state === self::MAIL_DELIVERY_FAILED;
	}

}
