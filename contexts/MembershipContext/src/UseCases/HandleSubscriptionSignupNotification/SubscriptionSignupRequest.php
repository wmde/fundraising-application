<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\MembershipContext\UseCases\HandleSubscriptionSignupNotification;

/**
 * @license GNU GPL v2+
 * @author Kai Nissen < kai.nissen@wikimedia.de >
 */
class SubscriptionSignupRequest {

	private $transactionType;

	private $subscriptionId;
	private $subscriptionDate;
	private $applicationId;

	private $payerId;
	private $payerStatus;
	private $payerFirstName;
	private $payerLastName;
	private $payerAddressName;
	private $payerAddressStreet;
	private $payerAddressPostalCode;
	private $payerAddressCity;
	private $payerAddressCountry;
	private $payerAddressStatus;
	private $payerEmail;

	private $paymentType;
	private $currencyCode;

	public function getTransactionType(): string {
		return $this->transactionType;
	}

	public function setTransactionType( string $transactionType ): self {
		$this->transactionType = $transactionType;
		return $this;
	}

	public function getSubscriptionId(): string {
		return $this->subscriptionId;
	}

	public function setSubscriptionId( string $subscriptionId ): self {
		$this->subscriptionId = $subscriptionId;
		return $this;
	}

	public function getSubscriptionDate(): string {
		return $this->subscriptionDate;
	}

	public function setSubscriptionDate( string $subscriptionDate ): self {
		$this->subscriptionDate = $subscriptionDate;
		return $this;
	}

	public function getApplicationId(): int {
		return $this->applicationId;
	}

	public function setApplicationId( int $applicationId ): self {
		$this->applicationId = $applicationId;
		return $this;
	}

	public function getPayerId(): string {
		return $this->payerId;
	}

	public function setPayerId( string $payerId ): self {
		$this->payerId = $payerId;
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

	public function getPayerAddressCity(): string {
		return $this->payerAddressCity;
	}

	public function setPayerAddressCity( string $payerAddressCity ): self {
		$this->payerAddressCity = $payerAddressCity;
		return $this;
	}

	public function getPayerAddressPostalCode(): string {
		return $this->payerAddressPostalCode;
	}

	public function setPayerAddressPostalCode( string $payerAddressPostalCode ): self {
		$this->payerAddressPostalCode = $payerAddressPostalCode;
		return $this;
	}

	public function getPayerAddressCountry(): string {
		return $this->payerAddressCountry;
	}

	public function setPayerAddressCountry( string $payerAddressCountry ): self {
		$this->payerAddressCountry = $payerAddressCountry;
		return $this;
	}

	public function getPayerAddressStatus(): string {
		return $this->payerAddressStatus;
	}

	public function setPayerAddressStatus( string $payerAddressStatus ): self {
		$this->payerAddressStatus = $payerAddressStatus;
		return $this;
	}

	public function getPayerEmail(): string {
		return $this->payerEmail;
	}

	public function setPayerEmail( string $payerEmail ): self {
		$this->payerEmail = $payerEmail;
		return $this;
	}

	public function getPaymentType(): string {
		return $this->paymentType;
	}

	public function setPaymentType( string $paymentType ): self {
		$this->paymentType = $paymentType;
		return $this;
	}

	public function getCurrencyCode(): string {
		return $this->currencyCode;
	}

	public function setCurrencyCode( string $currencyCode ): self {
		$this->currencyCode = $currencyCode;
		return $this;
	}

}
