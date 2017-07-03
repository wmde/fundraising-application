<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Infrastructure\Sofort\Transfer;

use WMDE\Euro\Euro;

class Request {

	/**
	 * @var Euro
	 */
	private $amount;
	/**
	 * @var string
	 */
	private $currencyCode = '';
	/**
	 * @var string
	 */
	private $successUrl = '';
	/**
	 * @var string
	 */
	private $abortUrl = '';
	/**
	 * @var string
	 */
	private $notificationUrl = '';
	/**
	 * @var string[]
	 */
	private $reasons = [ '', '' ];

	/**
	 * @return Euro
	 */
	public function getAmount(): Euro {
		return $this->amount;
	}

	/**
	 * @param Euro $amount
	 */
	public function setAmount( Euro $amount ): void {
		$this->amount = $amount;
	}

	/**
	 * @return string
	 */
	public function getCurrencyCode(): string {
		return $this->currencyCode;
	}

	/**
	 * @param string $currencyCode
	 */
	public function setCurrencyCode( string $currencyCode ): void {
		$this->currencyCode = $currencyCode;
	}

	/**
	 * @return string
	 */
	public function getSuccessUrl(): string {
		return $this->successUrl;
	}

	/**
	 * @param string $successUrl
	 */
	public function setSuccessUrl( string $successUrl ): void {
		$this->successUrl = $successUrl;
	}

	/**
	 * @return string
	 */
	public function getAbortUrl(): string {
		return $this->abortUrl;
	}

	/**
	 * @param string $abortUrl
	 */
	public function setAbortUrl( string $abortUrl ): void {
		$this->abortUrl = $abortUrl;
	}

	/**
	 * @return string
	 */
	public function getNotificationUrl(): string {
		return $this->notificationUrl;
	}

	/**
	 * @param string $notificationUrl
	 */
	public function setNotificationUrl( string $notificationUrl ): void {
		$this->notificationUrl = $notificationUrl;
	}

	/**
	 * @return string[]
	 */
	public function getReasons(): array {
		return $this->reasons;
	}

	/**
	 * @param string[] $reasons
	 */
	public function setReasons( array $reasons ): void {
		$this->reasons = $reasons;
	}
}
