<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\DonationContext\UseCases\CreditCardPaymentNotification;

/**
 * @license GNU GPL v2+
 * @author Kai Nissen < kai.nissen@wikimedia.de >
 */
class CreditCardNotificationResponse {

	private $donationId;
	private $accessToken;
	private $errorMessage;
	private $isSuccess;

	public const IS_SUCCESS = true;
	public const IS_FAILURE = false;

	public function __construct( int $donationId, string $accessToken, string $errorMessage, bool $isSuccess ) {
		$this->donationId = $donationId;
		$this->accessToken = $accessToken;
		$this->errorMessage = $errorMessage;
		$this->isSuccess = $isSuccess;
	}

	public static function newFailureResponse( string $errorMessage ): self {
		return new self( 0, '', $errorMessage, false );
	}

	public static function newSuccessResponse( int $donationId, string $accessToken ): self {
		return new self( $donationId, $accessToken, '', true );
	}

	public function getDonationId(): int {
		return $this->donationId;
	}

	public function getAccessToken(): string {
		return $this->accessToken;
	}

	public function getErrorMessage(): string {
		return $this->errorMessage;
	}

	public function isSuccessful(): bool {
		return $this->isSuccess;
	}

}
