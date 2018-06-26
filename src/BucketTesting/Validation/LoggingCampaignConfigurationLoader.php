<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\BucketTesting\Validation;

use FileFetcher\FileFetchingException;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\Yaml\Exception\ParseException;
use WMDE\Fundraising\Frontend\BucketTesting\CampaignConfigurationLoaderInterface;

class LoggingCampaignConfigurationLoader implements CampaignConfigurationLoaderInterface {

	private $actualCampaignConfigurationLoader;
	private $errorLogger;

	public function __construct( CampaignConfigurationLoaderInterface $campaignConfigurationLoader, ValidationErrorLogger $errorLogger ) {
		$this->errorLogger = $errorLogger;
		$this->actualCampaignConfigurationLoader = $campaignConfigurationLoader;
	}

	public function loadCampaignConfiguration( string ...$configFiles ): array {
		try {
			return $this->actualCampaignConfigurationLoader->loadCampaignConfiguration( ...$configFiles );
		}
		catch ( ParseException $e ) {
			$this->errorLogger->addError( 'Failed to parse campaign YAML: ' . $e->getMessage() );
		}
		catch ( FileFetchingException $e ) {
			$this->errorLogger->addError( 'YAML configuration file read error: ' . $e->getMessage() );
		}
		catch ( InvalidConfigurationException $e ) {
			$this->errorLogger->addError( 'Invalid campaign YAML configuration: ' . $e->getMessage() );
		}
		catch ( \RuntimeException $e ) {
			$this->errorLogger->addError( 'Runtime error while parsing campaign YAML: ' . $e->getMessage() );
		}
		catch ( \Throwable $e ) {
			$this->errorLogger->addError( get_class( $e ) . ': ' . $e->getMessage() );
		}
		return [];
	}
}