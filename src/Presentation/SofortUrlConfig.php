<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Presentation;

class SofortUrlConfig {

	private const CONFIG_CONFIGKEY = 'config-key';
	private const CONFIG_ITEMNAME = 'item-name';
	private const CONFIG_RETURNURL = 'return-url';
	private const CONFIG_CANCELURL = 'cancel-url';

	private $configkey;
	private $itemName;
	private $returnUrl;
	private $cancelUrl;

	private function __construct( string $configkey, string $itemName, string $returnUrl, string $cancelUrl ) {
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

	/**
	 * @param string[] $config
	 * @return PayPalUrlConfig
	 * @throws \RuntimeException
	 */
	public static function newFromConfig( array $config ): self {
		return ( new self(
			$config[self::CONFIG_CONFIGKEY],
			$config[self::CONFIG_ITEMNAME],
			$config[self::CONFIG_RETURNURL],
			$config[self::CONFIG_CANCELURL]
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
}
