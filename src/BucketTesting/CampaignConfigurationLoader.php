<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\BucketTesting;

use FileFetcher\FileFetcher;
use Psr\SimpleCache\CacheInterface;
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\Yaml\Yaml;
use WMDE\Fundraising\Frontend\Infrastructure\GetConfigCacheKey;

class CampaignConfigurationLoader implements CampaignConfigurationLoaderInterface {

	use GetConfigCacheKey;

	private FileFetcher $fileFetcher;
	private CacheInterface $cache;

	public function __construct( FileFetcher $fileFetcher, CacheInterface $cache ) {
		$this->fileFetcher = $fileFetcher;
		$this->cache = $cache;
	}

	public function loadCampaignConfiguration( string ...$configFiles ): array {
		$cacheKey = $this->getCacheKey( ...$configFiles );
		if ( $cacheKey !== '' && $this->cache->has( $cacheKey ) ) {
			return $this->cache->get( $cacheKey )['campaigns'];
		}
		$configs = $this->loadFiles( ...$configFiles );

		if ( count( $configs ) === 0 ) {
			throw new \RuntimeException( 'No campaign configuration files found (' . implode( ', ', $configFiles ) . ')' );
		}
		$processor = new Processor();
		$processedConfiguration = $processor->processConfiguration( new CampaignConfiguration(), $configs );
		$this->cache->set( $cacheKey, $processedConfiguration );
		return $processedConfiguration['campaigns'];
	}

	protected function loadFiles( string ...$configFiles ): array {
		$configs = [];
		foreach ( $configFiles as $file ) {
			if ( file_exists( $file ) ) {
				$configs[] = Yaml::parse( $this->fileFetcher->fetchFile( $file ) );
			}
		}
		return $configs;
	}

}
