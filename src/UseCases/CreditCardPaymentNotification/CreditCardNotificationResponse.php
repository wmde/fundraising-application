<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\UseCases\CreditCardPaymentNotification;

/**
 * @license GNU GPL v2+
 * @author Kai Nissen < kai.nissen@wikimedia.de >
 */
class CreditCardNotificationResponse {

	private $donationId;
	private $accessToken;
	private $isSuccess;

	const IS_SUCCESS = true;
	const IS_FAILURE = false;

	public function __construct( int $donationId, string $accessToken, bool $isSuccess ) {
		$this->donationId = $donationId;
		$this->accessToken = $accessToken;
		$this->isSuccess = $isSuccess;
	}

	public function getDonationId(): int {
		return $this->donationId;
	}

	public function getAccessToken(): string {
		return $this->accessToken;
	}

	public function isSuccessful(): bool {
		return $this->isSuccess;
	}

}
