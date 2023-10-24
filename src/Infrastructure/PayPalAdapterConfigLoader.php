<?php
declare( strict_types=1 );

namespace WMDE\Fundraising\Frontend\Infrastructure;

use Psr\SimpleCache\CacheInterface;
use WMDE\Fundraising\PaymentContext\Services\PayPal\PayPalPaymentProviderAdapterConfig;
use WMDE\Fundraising\PaymentContext\Services\PayPal\PayPalPaymentProviderAdapterConfigFactory;
use WMDE\Fundraising\PaymentContext\Services\PayPal\PayPalPaymentProviderAdapterConfigReader;

class PayPalAdapterConfigLoader {
	use GetConfigCacheKey;

	public function __construct( private readonly CacheInterface $cache ) {
	}

	public function load( string $configFile, string $productKey, string $locale ): PayPalPaymentProviderAdapterConfig {
		$cacheKey = $this->getCacheKey( $configFile );
		if ( $this->cache->has( $cacheKey ) ) {
			$configContents = $this->cache->get( $cacheKey );
		} else {
			$configContents = PayPalPaymentProviderAdapterConfigReader::readConfig( $configFile );
			$this->cache->set( $cacheKey, $configContents );
		}

		return PayPalPaymentProviderAdapterConfigFactory::createConfig( $configContents, $productKey, $locale );
	}

}
