<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\BucketTesting;

use Doctrine\Common\Cache\CacheProvider;
use FileFetcher\FileFetcher;
use FileFetcher\FileFetchingException;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Yaml\Exception\ParseException;
use Symfony\Component\Yaml\Yaml;

/**
 * @license GNU GPL v2+
 */
class CampaignConfigurationLoader implements CampaignConfigurationLoaderInterface {

	private $filesystem;
	private $fileFetcher;
	private $cache;

	public function __construct( Filesystem $filesystem, FileFetcher $fileFetcher, CacheProvider $cache ) {
		$this->filesystem = $filesystem;
		$this->fileFetcher = $fileFetcher;
		$this->cache = $cache;
	}

	/**
	 * @throws FileFetchingException
	 * @throws ParseException
	 * @throws \RuntimeException
	 * @throws InvalidConfigurationException
	 */
	public function loadCampaignConfiguration( string ...$configFiles ): array {
		$cacheKey = $this->getCacheKey( ...$configFiles );
		if ( $this->cache->contains( $cacheKey ) ) {
			return $this->cache->fetch( $cacheKey );
		}
		$configs = $this->loadFiles( ...$configFiles );

		if ( count( $configs ) === 0 ) {
			throw new \RuntimeException( 'No campaign configuration files found (' . implode( ', ', $configFiles ) . ')' );
		}
		$processor = new Processor();
		$processedConfiguration = $processor->processConfiguration( new CampaignConfiguration(), $configs );
		$this->cache->save( $cacheKey, $processedConfiguration );
		return $processedConfiguration;
	}

	protected function loadFiles( string ...$configFiles ): array {
		$configs = [];
		foreach ( $configFiles as $file ) {
			if ( $this->filesystem->exists( $file ) ) {
				$configs[] = Yaml::parse( $this->fileFetcher->fetchFile( $file ) );
			}
		}
		return $configs;
	}

	protected function getCacheKey( string ...$configFiles ): string {
		return md5( implode( '|', $configFiles ) );
	}

}