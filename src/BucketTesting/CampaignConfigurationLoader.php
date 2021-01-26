<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\BucketTesting;

use Doctrine\Common\Cache\CacheProvider;
use FileFetcher\FileFetcher;
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\Yaml\Yaml;

/**
 * @license GPL-2.0-or-later
 */
class CampaignConfigurationLoader implements CampaignConfigurationLoaderInterface {

	private FileFetcher $fileFetcher;
	private CacheProvider $cache;

	public function __construct( FileFetcher $fileFetcher, CacheProvider $cache ) {
		$this->fileFetcher = $fileFetcher;
		$this->cache = $cache;
	}

	public function loadCampaignConfiguration( string ...$configFiles ): array {
		$cacheKey = $this->getCacheKey( ...$configFiles );
		if ( $cacheKey !== '' && $this->cache->contains( $cacheKey ) ) {
			return $this->cache->fetch( $cacheKey );
		}
		$configs = $this->loadFiles( ...$configFiles );

		if ( count( $configs ) === 0 ) {
			throw new \RuntimeException( 'No campaign configuration files found (' . implode( ', ', $configFiles ) . ')' );
		}
		$processor = new Processor();
		$processedConfiguration = $processor->processConfiguration( new CampaignConfiguration(), $configs );
		$this->cache->save( $cacheKey, $processedConfiguration );
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

	/**
	 * Build a hash of file names and their last modification dates
	 *
	 * @param string ...$configFiles
	 * @return string
	 */
	protected function getCacheKey( string ...$configFiles ): string {
		$fileStats = '';
		foreach ( $configFiles as $file ) {
			if ( file_exists( $file ) ) {
				$fileStats .= sprintf( ",%s.%d", $file, filemtime( $file ) );
			}
		}
		return strlen( $fileStats ) > 0 ? md5( $fileStats ) : '';
	}

}
