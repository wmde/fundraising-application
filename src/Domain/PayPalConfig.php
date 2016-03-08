<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Domain;

/**
 * @licence GNU GPL v2+
 * @author Kai Nissen < kai.nissen@wikimedia.de >
 */
class PayPalConfig {

	private $payPalAccountAddress;
	private $payPalBaseUrl;
	private $notifyUrl;
	private $returnUrl;
	private $cancelUrl;
	private $itemName;

	private function __construct( string $payPalAccountAddress, string $payPalBaseUrl, string $notifyUrl,
								 string $returnUrl, string $cancelUrl, string $itemName ) {
		$this->payPalAccountAddress = $payPalAccountAddress;
		$this->payPalBaseUrl = $payPalBaseUrl;
		$this->notifyUrl = $notifyUrl;
		$this->returnUrl = $returnUrl;
		$this->cancelUrl = $cancelUrl;
		$this->itemName = $itemName;
	}

	/**
	 * @param string[] $config
	 * @return self
	 * @throws \RuntimeException
	 */
	public static function newFromConfig( array $config ): self {
		return ( new PayPalConfig(
			$config['account-address'],
			$config['base-url'],
			$config['notify-url'],
			$config['return-url'],
			$config['cancel-url'],
			$config['item-name']
		) )->assertNoEmptyFields();
	}

	private function assertNoEmptyFields(): self {
		foreach ( get_object_vars( $this ) as $fieldName => $fieldValue ) {
			if ( empty( $fieldValue ) ) {
				throw new \RuntimeException( "Configuration variable '$fieldName' can not be empty" );
			}
		}

		return $this;
	}

	public function getPayPalAccountAddress(): string {
		return $this->payPalAccountAddress;
	}

	public function getPayPalBaseUrl(): string {
		return $this->payPalBaseUrl;
	}

	public function getNotifyUrl(): string {
		return $this->notifyUrl;
	}

	public function getReturnUrl(): string {
		return $this->returnUrl;
	}

	public function getCancelUrl(): string {
		return $this->cancelUrl;
	}

	public function getItemName(): string {
		return $this->itemName;
	}

}
