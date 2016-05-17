<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Domain\Model;

use WMDE\Fundraising\Frontend\FreezableValueObject;

/**
 * @licence GNU GPL v2+
 * @author Kai Nissen < kai.nissen@wikimedia.de >
 */
class PayPalData {
	use FreezableValueObject;

	private $payerId = '';
	private $subscriberId = '';
	private $payerStatus = '';
	private $addressStatus = '';
	private $amount;
	private $currencyCode = '';
	private $fee;
	private $settleAmount;
	private $firstName = '';
	private $lastName = '';
	private $addressName = '';
	private $paymentId = '';
	private $paymentType = '';
	private $paymentStatus = '';
	private $paymentTimestamp = '';
	private $childPayments = [];

	public function __construct() {
		$this->amount = Euro::newFromInt( 0 );
		$this->fee = Euro::newFromInt( 0 );
		$this->settleAmount = Euro::newFromInt( 0 );
	}

	public function getPayerId(): string {
		return $this->payerId;
	}

	public function setPayerId( string $payerId ) {
		$this->assertIsWritable();
		$this->payerId = $payerId;
		return $this;
	}

	public function getSubscriberId(): string {
		return $this->subscriberId;
	}

	public function setSubscriberId( string $subscriberId ) {
		$this->assertIsWritable();
		$this->subscriberId = $subscriberId;
		return $this;
	}

	public function getPayerStatus(): string {
		return $this->payerStatus;
	}

	public function setPayerStatus( string $payerStatus ) {
		$this->assertIsWritable();
		$this->payerStatus = $payerStatus;
		return $this;
	}

	public function getAddressStatus(): string {
		return $this->addressStatus;
	}

	public function setAddressStatus( string $addressStatus ) {
		$this->assertIsWritable();
		$this->addressStatus = $addressStatus;
		return $this;
	}

	public function getAmount(): Euro {
		return $this->amount;
	}

	public function setAmount( Euro $amount ) {
		$this->assertIsWritable();
		$this->amount = $amount;
		return $this;
	}

	public function getCurrencyCode(): string {
		return $this->currencyCode;
	}

	public function setCurrencyCode( string $currencyCode ) {
		$this->assertIsWritable();
		$this->currencyCode = $currencyCode;
		return $this;
	}

	public function getFee(): Euro {
		return $this->fee;
	}

	public function setFee( Euro $fee ) {
		$this->assertIsWritable();
		$this->fee = $fee;
		return $this;
	}

	public function getSettleAmount(): Euro {
		return $this->settleAmount;
	}

	public function setSettleAmount( Euro $settleAmount ) {
		$this->assertIsWritable();
		$this->settleAmount = $settleAmount;
		return $this;
	}

	public function getFirstName(): string {
		return $this->firstName;
	}

	public function setFirstName( string $firstName ) {
		$this->assertIsWritable();
		$this->firstName = $firstName;
		return $this;
	}

	public function getLastName(): string {
		return $this->lastName;
	}

	public function setLastName( string $lastName ) {
		$this->assertIsWritable();
		$this->lastName = $lastName;
		return $this;
	}

	public function getAddressName(): string {
		return $this->addressName;
	}

	public function setAddressName( string $addressName ) {
		$this->assertIsWritable();
		$this->addressName = $addressName;
		return $this;
	}

	public function getPaymentId(): string {
		return $this->paymentId;
	}

	public function setPaymentId( string $paymentId ) {
		$this->assertIsWritable();
		$this->paymentId = $paymentId;
		return $this;
	}

	public function getPaymentType(): string {
		return $this->paymentType;
	}

	public function setPaymentType( string $paymentType ) {
		$this->assertIsWritable();
		$this->paymentType = $paymentType;
		return $this;
	}

	public function getPaymentStatus(): string {
		return $this->paymentStatus;
	}

	public function setPaymentStatus( string $paymentStatus ) {
		$this->assertIsWritable();
		$this->paymentStatus = $paymentStatus;
		return $this;
	}

	public function getPaymentTimestamp(): string {
		return $this->paymentTimestamp;
	}

	public function setPaymentTimestamp( string $paymentTimestamp ) {
		$this->assertIsWritable();
		$this->paymentTimestamp = $paymentTimestamp;
		return $this;
	}

	public function addChildPayment( string $paymentId, int $entityId ) {
		$this->childPayments[$paymentId] = $entityId;
		return $this;
	}
	public function hasChildPayment( string $paymentId ): bool {
		return isset( $this->childPayments[$paymentId] );
	}

}
