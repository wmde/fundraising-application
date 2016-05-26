<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\UseCases\HandlePayPalPaymentNotification;

use WMDE\Fundraising\Frontend\Domain\Model\Euro;

/**
 * @license GNU GPL v2+
 * @author Kai Nissen < kai.nissen@wikimedia.de >
 */
class PayPalNotificationRequest {

	private $transactionType;
	private $transactionId;

	private $payerId;
	private $payerEmail;
	private $payerStatus;
	private $subscriberId;

	private $payerFirstName;
	private $payerLastName;
	private $payerAddressName;
	private $payerAddressStreet;
	private $payerAddressPostalCode;
	private $payerAddressCity;
	private $payerAddressCountryCode;
	private $payerAddressStatus;

	private $donationId;
	private $token;

	private $currencyCode;
	private $transactionFee;
	private $amountGross;
	private $settleAmount;
	private $paymentTimestamp;
	private $paymentStatus;
	private $paymentType;

	public function getTransactionType(): string {
		return $this->transactionType;
	}

	public function setTransactionType( string $transactionType ): self {
		$this->transactionType = $transactionType;
		return $this;
	}

	public function getTransactionId(): string {
		return $this->transactionId;
	}

	public function setTransactionId( string $transactionId ): self {
		$this->transactionId = $transactionId;
		return $this;
	}

	public function getPayerId(): string {
		return $this->payerId;
	}

	public function setPayerId( string $payerId ): self {
		$this->payerId = $payerId;
		return $this;
	}

	public function getSubscriberId(): string {
		return $this->subscriberId;
	}

	public function setSubscriberId( string $subscriberId ) {
		$this->subscriberId = $subscriberId;
		return $this;
	}

	public function getPayerEmail(): string {
		return $this->payerEmail;
	}

	public function setPayerEmail( string $payerEmail ): self {
		$this->payerEmail = $payerEmail;
		return $this;
	}

	public function getPayerStatus(): string {
		return $this->payerStatus;
	}

	public function setPayerStatus( string $payerStatus ): self {
		$this->payerStatus = $payerStatus;
		return $this;
	}

	public function getPayerFirstName(): string {
		return $this->payerFirstName;
	}

	public function setPayerFirstName( string $payerFirstName ): self {
		$this->payerFirstName = $payerFirstName;
		return $this;
	}

	public function getPayerLastName(): string {
		return $this->payerLastName;
	}

	public function setPayerLastName( string $payerLastName ): self {
		$this->payerLastName = $payerLastName;
		return $this;
	}

	public function getPayerAddressName(): string {
		return $this->payerAddressName;
	}

	public function setPayerAddressName( string $payerAddressName ): self {
		$this->payerAddressName = $payerAddressName;
		return $this;
	}

	public function getPayerAddressStreet(): string {
		return $this->payerAddressStreet;
	}

	public function setPayerAddressStreet( string $payerAddressStreet ): self {
		$this->payerAddressStreet = $payerAddressStreet;
		return $this;
	}

	public function getPayerAddressPostalCode(): string {
		return $this->payerAddressPostalCode;
	}

	public function setPayerAddressPostalCode( string $payerAddressPostalCode ): self {
		$this->payerAddressPostalCode = $payerAddressPostalCode;
		return $this;
	}

	public function getPayerAddressCity(): string {
		return $this->payerAddressCity;
	}

	public function setPayerAddressCity( string $payerAddressCity ): self {
		$this->payerAddressCity = $payerAddressCity;
		return $this;
	}

	public function getPayerAddressCountryCode(): string {
		return $this->payerAddressCountryCode;
	}

	public function setPayerAddressCountryCode( string $payerAddressCountryCode ): self {
		$this->payerAddressCountryCode = $payerAddressCountryCode;
		return $this;
	}

	public function getPayerAddressStatus(): string {
		return $this->payerAddressStatus;
	}

	public function setPayerAddressStatus( string $payerAddressStatus ): self {
		$this->payerAddressStatus = $payerAddressStatus;
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

	public function getCurrencyCode(): string {
		return $this->currencyCode;
	}

	public function setCurrencyCode( string $currencyCode ): self {
		$this->currencyCode = $currencyCode;
		return $this;
	}

	public function getTransactionFee(): Euro {
		return $this->transactionFee;
	}

	public function setTransactionFee( Euro $transactionFee ): self {
		$this->transactionFee = $transactionFee;
		return $this;
	}

	public function getAmountGross(): Euro {
		return $this->amountGross;
	}

	public function setAmountGross( Euro $amountGross ): self {
		$this->amountGross = $amountGross;
		return $this;
	}

	public function getPaymentTimestamp(): string {
		return $this->paymentTimestamp;
	}

	public function setPaymentTimestamp( string $paymentTimestamp ): self {
		$this->paymentTimestamp = $paymentTimestamp;
		return $this;
	}

	public function getPaymentStatus(): string {
		return $this->paymentStatus;
	}

	public function setPaymentStatus( string $paymentStatus ): self {
		$this->paymentStatus = $paymentStatus;
		return $this;
	}

	public function getPaymentType(): string {
		return $this->paymentType;
	}

	public function setPaymentType( string $paymentType ): self {
		$this->paymentType = $paymentType;
		return $this;
	}

	public function getSettleAmount() {
		return $this->settleAmount;
	}

	public function setSettleAmount( Euro $settleAmount ) {
		$this->settleAmount = $settleAmount;
		return $this;
	}

	public function isSuccessfulPaymentNotification(): bool {
		return $this->paymentStatus === 'Completed' || $this->paymentStatus === 'Processed';
	}

	public function isRecurringPaymentCompletion(): bool {
		return $this->transactionType === 'subscr_payment';
	}

	public function isForRecurringPayment(): bool {
		return strpos( $this->transactionType, 'subscr_' ) === 0;
	}

}
