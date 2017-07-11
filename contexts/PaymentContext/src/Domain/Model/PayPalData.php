<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\PaymentContext\Domain\Model;

use WMDE\Euro\Euro;
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
	private $firstPaymentDate = '';
	private $childPayments = [];

	public function __construct() {
		$this->amount = Euro::newFromInt( 0 );
		$this->fee = Euro::newFromInt( 0 );
		$this->settleAmount = Euro::newFromInt( 0 );
	}

	public function getPayerId(): string {
		return $this->payerId;
	}

	public function setPayerId( string $payerId ): self {
		$this->assertIsWritable();
		$this->payerId = $payerId;
		return $this;
	}

	public function getSubscriberId(): string {
		return $this->subscriberId;
	}

	public function setSubscriberId( string $subscriberId ): self {
		$this->assertIsWritable();
		$this->subscriberId = $subscriberId;
		return $this;
	}

	public function getPayerStatus(): string {
		return $this->payerStatus;
	}

	public function setPayerStatus( string $payerStatus ): self {
		$this->assertIsWritable();
		$this->payerStatus = $payerStatus;
		return $this;
	}

	public function getAddressStatus(): string {
		return $this->addressStatus;
	}

	public function setAddressStatus( string $addressStatus ): self {
		$this->assertIsWritable();
		$this->addressStatus = $addressStatus;
		return $this;
	}

	public function getAmount(): Euro {
		return $this->amount;
	}

	public function setAmount( Euro $amount ): self {
		$this->assertIsWritable();
		$this->amount = $amount;
		return $this;
	}

	public function getCurrencyCode(): string {
		return $this->currencyCode;
	}

	public function setCurrencyCode( string $currencyCode ): self {
		$this->assertIsWritable();
		$this->currencyCode = $currencyCode;
		return $this;
	}

	public function getFee(): Euro {
		return $this->fee;
	}

	public function setFee( Euro $fee ): self {
		$this->assertIsWritable();
		$this->fee = $fee;
		return $this;
	}

	public function getSettleAmount(): Euro {
		return $this->settleAmount;
	}

	public function setSettleAmount( Euro $settleAmount ): self {
		$this->assertIsWritable();
		$this->settleAmount = $settleAmount;
		return $this;
	}

	public function getFirstName(): string {
		return $this->firstName;
	}

	public function setFirstName( string $firstName ): self {
		$this->assertIsWritable();
		$this->firstName = $firstName;
		return $this;
	}

	public function getLastName(): string {
		return $this->lastName;
	}

	public function setLastName( string $lastName ): self {
		$this->assertIsWritable();
		$this->lastName = $lastName;
		return $this;
	}

	public function getAddressName(): string {
		return $this->addressName;
	}

	public function setAddressName( string $addressName ): self {
		$this->assertIsWritable();
		$this->addressName = $addressName;
		return $this;
	}

	public function getPaymentId(): string {
		return $this->paymentId;
	}

	public function setPaymentId( string $paymentId ): self {
		$this->assertIsWritable();
		$this->paymentId = $paymentId;
		return $this;
	}

	public function getPaymentType(): string {
		return $this->paymentType;
	}

	public function setPaymentType( string $paymentType ): self {
		$this->assertIsWritable();
		$this->paymentType = $paymentType;
		return $this;
	}

	public function getPaymentStatus(): string {
		return $this->paymentStatus;
	}

	public function setPaymentStatus( string $paymentStatus ): self {
		$this->assertIsWritable();
		$this->paymentStatus = $paymentStatus;
		return $this;
	}

	public function getPaymentTimestamp(): string {
		return $this->paymentTimestamp;
	}

	public function setPaymentTimestamp( string $paymentTimestamp ): self {
		$this->assertIsWritable();
		$this->paymentTimestamp = $paymentTimestamp;
		return $this;
	}

	public function getFirstPaymentDate(): string {
		return $this->firstPaymentDate;
	}

	public function setFirstPaymentDate( string $firstPaymentDate ): self {
		$this->assertIsWritable();
		$this->firstPaymentDate = $firstPaymentDate;
		return $this;
	}

	public function addChildPayment( string $paymentId, int $entityId ): self {
		$this->childPayments[$paymentId] = $entityId;
		return $this;
	}

	public function hasChildPayment( string $paymentId ): bool {
		return isset( $this->childPayments[$paymentId] );
	}

	public function getChildPaymentEntityId( string $paymentId ): int {
		return $this->childPayments[$paymentId];
	}

}
