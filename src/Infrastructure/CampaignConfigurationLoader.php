<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Infrastructure;

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
class CampaignConfigurationLoader {

	private $filesystem;
	private $fileFetcher;

	public function __construct( Filesystem $filesystem, FileFetcher $fileFetcher ) {
		$this->filesystem = $filesystem;
		$this->fileFetcher = $fileFetcher;
	}

	/**
	 * @throws FileFetchingException
	 * @throws ParseException
	 * @throws \RuntimeException
	 * @throws InvalidConfigurationException
	 */
	public function loadCampaignConfiguration( string ...$configFiles ): array {
		$configs = [];
		foreach ( $configFiles as $file ) {
			if ( $this->filesystem->exists( $file ) ) {
				$configs[] = Yaml::parse( $this->fileFetcher->fetchFile( $file ) );
			}
		}
		if ( count( $configs ) === 0 ) {
			throw new \RuntimeException( 'No campaign configuration files found (' . implode( ', ', $configFiles ) . ')' );
		}
		$processor = new Processor();
		return $processor->processConfiguration( new CampaignConfiguration(), $configs );
	}

}