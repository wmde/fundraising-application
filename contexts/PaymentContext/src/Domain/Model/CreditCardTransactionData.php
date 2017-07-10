<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\PaymentContext\Domain\Model;

use WMDE\Euro\Euro;
use WMDE\Fundraising\Frontend\FreezableValueObject;
use WMDE\Fundraising\Frontend\PaymentContext\Infrastructure\CreditCardExpiry;

/**
 * @licence GNU GPL v2+
 * @author Kai Nissen < kai.nissen@wikimedia.de >
 */
class CreditCardTransactionData {
	use FreezableValueObject;

	private $transactionId = '';
	private $transactionStatus = '';
	private $transactionTimestamp;
	private $authId = '';
	private $sessionId = '';
	private $customerId = '';
	private $cardExpiry;

	private $currencyCode = '';
	private $amount;
	private $countryCode = '';
	private $title = '';

	public function __construct() {
		$this->amount = Euro::newFromInt( 0 );
		$this->transactionTimestamp = new \DateTime();
	}

	public function getTransactionId(): string {
		return $this->transactionId;
	}

	public function setTransactionId( string $transactionId ): self {
		$this->assertIsWritable();
		$this->transactionId = $transactionId;
		return $this;
	}

	public function getTransactionStatus(): string {
		return $this->transactionStatus;
	}

	public function setTransactionStatus( string $transactionStatus ): self {
		$this->assertIsWritable();
		$this->transactionStatus = $transactionStatus;
		return $this;
	}

	public function getTransactionTimestamp(): \DateTime {
		return $this->transactionTimestamp;
	}

	public function setTransactionTimestamp( \DateTime $transactionTimestamp ): self {
		$this->assertIsWritable();
		$this->transactionTimestamp = $transactionTimestamp;
		return $this;
	}

	public function getAuthId(): string {
		return $this->authId;
	}

	public function setAuthId( string $authId ): self {
		$this->assertIsWritable();
		$this->authId = $authId;
		return $this;
	}

	public function getSessionId(): string {
		return $this->sessionId;
	}

	public function setSessionId( string $sessionId ): self {
		$this->assertIsWritable();
		$this->sessionId = $sessionId;
		return $this;
	}

	public function getCustomerId(): string {
		return $this->customerId;
	}

	public function setCustomerId( string $customerId ): self {
		$this->assertIsWritable();
		$this->customerId = $customerId;
		return $this;
	}

	public function getCardExpiry(): ?CreditCardExpiry {
		return $this->cardExpiry;
	}

	public function setCardExpiry( ?CreditCardExpiry $cardExpiry ): self {
		$this->assertIsWritable();
		$this->cardExpiry = $cardExpiry;
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

	public function getCountryCode(): string {
		return $this->countryCode;
	}

	public function setCountryCode( string $countryCode ): self {
		$this->assertIsWritable();
		$this->countryCode = $countryCode;
		return $this;
	}

	public function getTitle(): string {
		return $this->title;
	}

	public function setTitle( string $title ): self {
		$this->assertIsWritable();
		$this->title = $title;
		return $this;
	}

}
