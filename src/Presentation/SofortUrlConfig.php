<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Presentation;

class SofortUrlConfig {

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

	public function __construct( string $reasonText, string $returnUrl, string $cancelUrl ) {
		$this->reasonText = $reasonText;
		$this->returnUrl = $returnUrl;
		$this->cancelUrl = $cancelUrl;
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
}
