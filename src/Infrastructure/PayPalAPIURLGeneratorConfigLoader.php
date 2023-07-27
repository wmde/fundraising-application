<?php
declare( strict_types=1 );

namespace WMDE\Fundraising\Frontend\Infrastructure;

use Psr\SimpleCache\CacheInterface;
use WMDE\Fundraising\PaymentContext\Domain\PaymentUrlGenerator\PayPalAPIURLGeneratorConfig;
use WMDE\Fundraising\PaymentContext\Domain\PaymentUrlGenerator\PayPalAPIURLGeneratorConfigFactory;
use WMDE\Fundraising\PaymentContext\Services\PayPal\PayPalURLGeneratorConfigReader;

class PayPalAPIURLGeneratorConfigLoader {
	use GetConfigCacheKey;

	public function __construct( private readonly CacheInterface $cache ) {
	}

	public function load( string $configFile, string $productKey, string $locale ): PayPalAPIURLGeneratorConfig {
		$cacheKey = $this->getCacheKey( $configFile );
		if ( $this->cache->has( $cacheKey ) ) {
			$configContents = $this->cache->get( $cacheKey );
		} else {
			$configContents = PayPalURLGeneratorConfigReader::readConfig( $configFile );
			$this->cache->set( $cacheKey, $configContents );
		}

		return PayPalAPIURLGeneratorConfigFactory::createConfig( $configContents, $productKey, $locale );
	}

}
