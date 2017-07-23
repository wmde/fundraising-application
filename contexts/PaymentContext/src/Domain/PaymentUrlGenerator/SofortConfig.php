<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\PaymentContext\Domain\PaymentUrlGenerator;

class SofortConfig {

	/**
	 * @var string
	 */
	private $reasonText;
	/**
	 * @var string
	 */
	private $returnUrl;
	/**
	 * @var string
	 */
	private $cancelUrl;
	/**
	 * @var string
	 */
	private $notificationUrl;

	public function __construct( string $reasonText, string $returnUrl, string $cancelUrl, string $notificationUrl ) {
		$this->reasonText = $reasonText;
		$this->returnUrl = $returnUrl;
		$this->cancelUrl = $cancelUrl;
		$this->notificationUrl = $notificationUrl;
	}

	public function getReasonText(): string {
		return $this->reasonText;
	}

	public function getReturnUrl(): string {
		return $this->returnUrl;
	}

	public function getCancelUrl(): string {
		return $this->cancelUrl;
	}

	public function getNotificationUrl(): string {
		return $this->notificationUrl;
	}
}
