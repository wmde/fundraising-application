<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Presentation;

class SofortUrlConfig {

	private $configkey;
	private $itemName;
	private $returnUrl;
	private $cancelUrl;

	public function __construct( string $configkey, string $itemName, string $returnUrl, string $cancelUrl ) {
		$this->configkey = $configkey;
		$this->returnUrl = $returnUrl;
		$this->cancelUrl = $cancelUrl;
		$this->itemName = $itemName;
	}

	public function getConfigkey(): string {
		return $this->configkey;
	}

	public function getItemName(): string {
		return $this->itemName;
	}

	public function getReturnUrl(): string {
		return $this->returnUrl;
	}

	public function getCancelUrl(): string {
		return $this->cancelUrl;
	}
}
