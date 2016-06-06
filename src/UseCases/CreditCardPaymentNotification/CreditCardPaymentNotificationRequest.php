<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\UseCases\CreditCardPaymentNotification;

use WMDE\Fundraising\Frontend\Domain\Model\Euro;

/**
 * @license GNU GPL v2+
 * @author Kai Nissen < kai.nissen@wikimedia.de >
 */
class CreditCardPaymentNotificationRequest {

	const NOTIFICATION_TYPE_BILLING = 'billing';
	const NOTIFICATION_TYPE_ERROR = 'error';

	private $transactionId;
	private $notificationType;
	private $amount;
	private $customerId;
	private $sessionId;
	private $authId;

	private $donationId;
	private $token;
	private $updateToken;

	private $title;
	private $country;
	private $currency;

	public function getTransactionId(): string {
		return $this->transactionId;
	}

	public function setTransactionId( string $transactionId ): self {
		$this->transactionId = $transactionId;
		return $this;
	}

	public function getNotificationType(): string {
		return $this->notificationType;
	}

	public function setNotificationType( string $notificationType ): self {
		$this->notificationType = $notificationType;
		return $this;
	}

	public function getAmount(): Euro {
		return $this->amount;
	}

	public function setAmount( Euro $amount ): self {
		$this->amount = $amount;
		return $this;
	}

	public function getCustomerId(): string {
		return $this->customerId;
	}

	public function setCustomerId( string $customerId ): self {
		$this->customerId = $customerId;
		return $this;
	}

	public function getSessionId(): string {
		return $this->sessionId;
	}

	public function setSessionId( string $sessionId ): self {
		$this->sessionId = $sessionId;
		return $this;
	}

	public function getAuthId(): string {
		return $this->authId;
	}

	public function setAuthId( string $authId ): self {
		$this->authId = $authId;
		return $this;
	}

	public function getDonationId(): int {
		return $this->donationId;
	}

	public function setDonationId( int $donationId ): self {
		$this->donationId = $donationId;
		return $this;
	}

	public function getToken(): string {
		return $this->token;
	}

	public function setToken( string $token ): self {
		$this->token = $token;
		return $this;
	}

	public function getUpdateToken(): string {
		return $this->updateToken;
	}

	public function setUpdateToken( string $updateToken ): self {
		$this->updateToken = $updateToken;
		return $this;
	}

	public function getTitle(): string {
		return $this->title;
	}

	public function setTitle( string $title ): self {
		$this->title = $title;
		return $this;
	}

	public function getCountry(): string {
		return $this->country;
	}

	public function setCountry( string $country ): self {
		$this->country = $country;
		return $this;
	}

	public function getCurrency(): string {
		return $this->currency;
	}

	public function setCurrency( string $currency ): self {
		$this->currency = $currency;
		return $this;
	}

}
