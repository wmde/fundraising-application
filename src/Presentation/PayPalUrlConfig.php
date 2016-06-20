<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Presentation;

/**
 * @licence GNU GPL v2+
 * @author Kai Nissen < kai.nissen@wikimedia.de >
 */
class PayPalUrlConfig {

	const CONFIG_KEY_ACCOUNT_ADDRESS = 'account-address';
	const CONFIG_KEY_BASE_URL = 'base-url';
	const CONFIG_KEY_NOTIFY_URL = 'notify-url';
	const CONFIG_KEY_RETURN_URL = 'return-url';
	const CONFIG_KEY_CANCEL_URL = 'cancel-url';
	const CONFIG_KEY_ITEM_NAME = 'item-name';

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
	 * @return PayPalUrlConfig
	 * @throws \RuntimeException
	 */
	public static function newFromConfig( array $config ): self {
		return ( new PayPalUrlConfig(
			$config[self::CONFIG_KEY_ACCOUNT_ADDRESS],
			$config[self::CONFIG_KEY_BASE_URL],
			$config[self::CONFIG_KEY_NOTIFY_URL],
			$config[self::CONFIG_KEY_RETURN_URL],
			$config[self::CONFIG_KEY_CANCEL_URL],
			$config[self::CONFIG_KEY_ITEM_NAME]
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
